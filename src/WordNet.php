<?php

namespace olcaytaner\WordNet;

use olcaytaner\Dictionary\Dictionary\ExceptionalWord;
use olcaytaner\Dictionary\Dictionary\Pos;
use olcaytaner\MorphologicalAnalysis\MorphologicalAnalysis\FsmMorphologicalAnalyzer;
use olcaytaner\MorphologicalAnalysis\MorphologicalAnalysis\MetamorphicParse;
use olcaytaner\MorphologicalAnalysis\MorphologicalAnalysis\MorphologicalParse;
use olcaytaner\XmlParser\XmlDocument;

class WordNet
{
    private array $synSetList = [];
    private array $literalList = [];
    private array $exceptionList = [];
    private array $interlingualList = [];
    
    private string $locale;

    /**
     * Reads a wordnet from a Xml file. A wordnet consists of a list of synsets encapsulated inside SYNSET tag. A synset
     * has an id (represented with ID tag), a set of literals encapsulated inside SYNONYM tag, part of speech tag
     * (represented with POS tag), a set of semantic relations encapsulated inside SR tag, a definition (represented
     * with DEF tag), and a possible example (represented with EXAMPLE tag). Each literal has a name, possibly a group
     * number (represented with GROUP tag), a sense number (represented with SENSE tag) and a set of semantic relations
     * encapsulated inside SR tag. A semantic relation has a name and a type (represented with TYPE tag).
     * @param string|null $fileName File stream that contains the wordnet.
     * @param string|null $locale Locale string, "tr" for Turkish.
     */
    public function __construct(?string $fileName = null, ?string $locale = null)
    {
        if ($fileName == null) {
            $fileName = "../turkish_wordnet.xml";
        } else {
            if ($locale == null) {
                $this->locale = "en";
                $this->readExceptionFile("../english_exception.xml");
            } else {
                $this->locale = $locale;
            }
        }
        $doc = new XmlDocument($fileName);
        $doc->parse();
        $rootNode = $doc->getFirstChild();
        $synSetNode = $rootNode->getFirstChild();
        while ($synSetNode != null) {
            $partNode = $synSetNode->getFirstChild();
            while ($partNode != null) {
                if ($partNode->getName() == "ID") {
                    $currentSynSet = new SynSet($partNode->getPcData());
                    $this->addSynSet($currentSynSet);
                } else {
                    if ($partNode->getName() == "DEF") {
                        $currentSynSet->setDefinition($partNode->getPcData());
                    } else {
                        if ($partNode->getName() == "EXAMPLE") {
                            $currentSynSet->setExample($partNode->getPcData());
                        } else {
                            if ($partNode->getName() == "BCS") {
                                $currentSynSet->setBcs($partNode->getPcData());
                            } else {
                                if ($partNode->getName() == "POS") {
                                    switch ($partNode->getPcData()[0]) {
                                        case 'a':
                                            $currentSynSet->setPos(Pos::ADJECTIVE);
                                            break;
                                        case 'v':
                                            $currentSynSet->setPos(Pos::VERB);
                                            break;
                                        case 'b':
                                            $currentSynSet->setPos(Pos::ADVERB);
                                            break;
                                        case 'n':
                                            $currentSynSet->setPos(Pos::NOUN);
                                            break;
                                        case 'i':
                                            $currentSynSet->setPos(Pos::INTERJECTION);
                                            break;
                                        case 'c':
                                            $currentSynSet->setPos(Pos::CONJUNCTION);
                                            break;
                                        case 'p':
                                            $currentSynSet->setPos(Pos::PREPOSITION);
                                            break;
                                        case 'r':
                                            $currentSynSet->setPos(Pos::PRONOUN);
                                            break;
                                    }
                                } else {
                                    if ($partNode->getName() == "SR") {
                                        $typeNode = $partNode->getFirstChild();
                                        if ($typeNode != null && $typeNode->getName() == "TYPE") {
                                            $toNode = $typeNode->getNextSibling();
                                            if ($toNode != null && $toNode->getName() == "TO") {
                                                $currentSynSet->addRelation(new SemanticRelation($partNode->getPcData(), $typeNode->getPcData(), $toNode->getPcData()));
                                            } else {
                                                $currentSynSet->addRelation(new SemanticRelation($partNode->getPcData(), $typeNode->getPcData()));
                                            }
                                        }
                                    } else {
                                        if ($partNode->getName() == "ILR") {
                                            $typeNode = $partNode->getFirstChild();
                                            if ($typeNode != null && $typeNode->getName() == "TYPE") {
                                                $interlingualId = $partNode->getPcData();
                                                if (array_key_exists($interlingualId, $this->interlingualList)) {
                                                    $sList = $this->interlingualList[$interlingualId];
                                                } else {
                                                    $sList = [];
                                                }
                                                $sList[] = $currentSynSet;
                                                $this->interlingualList[$interlingualId] = $sList;
                                                $currentSynSet->addRelation(new InterlingualRelation($interlingualId, $typeNode->getPcData()));
                                            }
                                        } else {
                                            if ($partNode->getName() == "SYNONYM") {
                                                $literalNode = $partNode->getFirstChild();
                                                while ($literalNode != null) {
                                                    if ($literalNode->getName() == "LITERAL") {
                                                        $senseNode = $literalNode->getFirstChild();
                                                        if ($senseNode != null) {
                                                            if ($senseNode->getName() == "SENSE" && $senseNode->getPcData() != "") {
                                                                $currentLiteral = new Literal($literalNode->getPcData(), $senseNode->getPcData(), $currentSynSet->getId());
                                                                $srNode = $senseNode->getNextSibling();
                                                                while ($srNode != null) {
                                                                    if ($srNode->getName() == "SR") {
                                                                        $typeNode = $srNode->getFirstChild();
                                                                        if ($typeNode != null && $typeNode->getName() == "TYPE") {
                                                                            $toNode = $typeNode->getNextSibling();
                                                                            if ($toNode != null && $toNode->getName() == "TO") {
                                                                                $currentLiteral->addRelation(new SemanticRelation($srNode->getPcData(), $typeNode->getPcData(), $toNode->getPcData()));
                                                                            } else {
                                                                                $currentLiteral->addRelation(new SemanticRelation($srNode->getPcData(), $typeNode->getPcData()));
                                                                            }
                                                                        }
                                                                    } else {
                                                                        if ($srNode->getName() == "ORIGIN") {
                                                                            $currentLiteral->setOrigin($srNode->getPcData());
                                                                        } else {
                                                                            if ($srNode->getName() == "GROUP") {
                                                                                $currentLiteral->setGroupNo($senseNode->getPcData());
                                                                            }
                                                                        }
                                                                    }
                                                                    $srNode = $srNode->getNextSibling();
                                                                }
                                                                $currentSynSet->addLiteral($currentLiteral);
                                                                $this->addLiteralToLiteralList($currentLiteral);
                                                            }
                                                        }
                                                    }
                                                    $literalNode = $literalNode->getNextSibling();
                                                }
                                            } else {
                                                if ($partNode->getName() == "SNOTE") {
                                                    $currentSynSet->setNote($partNode->getPcData());
                                                } else {
                                                    if ($partNode->getName() == "WIKI") {
                                                        $currentSynSet->setWikiPage($partNode->getPcData());
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                $partNode = $partNode->getNextSibling();
            }
            $synSetNode = $synSetNode->getNextSibling();
        }
    }

    /**
     * Method constructs a DOM parser using the dtd/xml schema parser configuration and using this parser it
     * reads exceptions from file and puts to exceptionList HashMap.
     *
     * @param string $exceptionFileName exception file to be read
     */
    public function readExceptionFile(string $exceptionFileName): void
    {
        $doc = new XmlDocument($exceptionFileName);
        $doc->parse();
        $rootNode = $doc->getFirstChild();
        $wordNode = $rootNode->getFirstChild();
        while ($wordNode != null) {
            if ($wordNode->hasAttributes()) {
                $wordName = $wordNode->getAttributeValue("name");
                $rootForm = $wordNode->getAttributeValue("root");
                if ($wordNode->getAttributeValue("pos") == "Adj") {
                    $pos = Pos::ADJECTIVE;
                } else {
                    if ($wordNode->getAttributeValue("pos") == "Adv") {
                        $pos = Pos::ADVERB;
                    } else {
                        if ($wordNode->getAttributeValue("pos") == "Noun") {
                            $pos = Pos::NOUN;
                        } else {
                            if ($wordNode->getAttributeValue("pos") == "Verb") {
                                $pos = Pos::VERB;
                            } else {
                                $pos = Pos::NOUN;
                            }
                        }
                    }
                }
                if (array_key_exists($wordName, $this->exceptionList)) {
                    $rootList = $this->exceptionList[$wordName];
                } else {
                    $rootList = [];
                }
                $rootList[] = new ExceptionalWord($wordName, $rootForm, $pos);
                $this->exceptionList[$wordName] = $rootList;
            }
            $wordNode = $wordNode->getNextSibling();
        }
    }

    /**
     * Adds a specified literal to the literal list.
     *
     * @param Literal $literal literal to be added
     */
    public function addLiteralToLiteralList(Literal $literal): void
    {
        if (array_key_exists($literal->getName(), $this->literalList)) {
            $literals = $this->literalList[$literal->getName()];
        } else {
            $literals = [];
        }
        $literals[] = $literal;
        $this->literalList[$literal->getName()] = $literals;
    }

    /**
     * Returns the $locale.
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * Updates the wordnet according to the situation that an old synset replaced with a new synset. There are three
     * possibilities: (i) The new synset has a relation with the old synset, then the relation is removed,
     * (ii) A synset has the same type of relation with old synset and new synset, then the relation is removed,
     * (iii) None of the above, then the old synset id in the relation is replaced with the new synset id.
     * @param SynSet $oldSynSet Old synset to be replaced
     * @param SynSet $newSynSet New synset replacing the old synset
     */
    private function updateAllRelationsAccordingToNewSynSet(SynSet $oldSynSet, SynSet $newSynSet): void
    {
        foreach ($this->getSynSetList() as $synSet) {
            for ($i = 0; $i < $synSet->relationSize(); $i++) {
                if ($synSet->getRelation($i) instanceof SemanticRelation) {
                    if ($synSet->getRelation($i)->getName() == $oldSynSet->getId()) {
                        if ($synSet->getId() == $newSynSet->getId() || $synSet->containsRelation(new SemanticRelation($newSynSet->getId(), ($synSet->getRelation($i))->getRelationType()))) {
                            $synSet->removeRelation($synSet->getRelation($i));
                            $i--;
                        } else {
                            $synSet->getRelation($i)->setName($newSynSet->getId());
                        }
                    }
                }
            }
        }
    }

    /**
     * Returns the values of the SynSet list.
     *
     * @return array values of the SynSet list
     */
    public function getSynSetList(): array
    {
        return array_values($this->synSetList);
    }

    /**
     * Returns the keys of the literal list.
     *
     * @return array keys of the literal list
     */
    public function getLiteralList(): array
    {
        return array_keys($this->literalList);
    }

    /**
     * Adds specified SynSet to the SynSet list.
     *
     * @param SynSet $synSet SynSet to be added
     */
    public function addSynSet(SynSet $synSet): void
    {
        $this->synSetList[$synSet->getId()] = $synSet;
    }

    /**
     * Removes specified SynSet from the SynSet list.
     *
     * @param SynSet $synSet SynSet to be removed
     */
    public function removeSynSet(SynSet $synSet): void
    {
        unset($this->synSetList[$synSet->getId()]);
    }

    /**
     * Removes specified SynSet from the SynSet list.
     *
     * @param SynSet $synSet SynSet to be removed
     */
    public function removeSynSetWithRelations(SynSet $synSet): void
    {
        for ($i = 0; $i < $synSet->relationSize(); $i++) {
            if ($synSet->getRelation($i) instanceof SemanticRelation) {
                $relation = $synSet->getRelation($i);
                $this->removeReverseRelation($synSet, $relation);
            }
        }
        unset($this->synSetList[$synSet->getId()]);
    }

    /**
     * Changes ID of a specified SynSet with the specified new ID.
     *
     * @param SynSet $synSet SynSet whose ID will be updated
     * @param string $newId new ID
     */
    public function changeSynSetId(SynSet $synSet, string $newId): void
    {
        unset($this->synSetList[$synSet->getId()]);
        $synSet->setId($newId);
        $this->synSetList[$newId] = $synSet;
    }

    /**
     * Returns SynSet with the specified SynSet ID.
     *
     * @param string $synSetId ID of the SynSet to be returned
     * @return SynSet|null with the specified SynSet ID
     */
    public function getSynSetWithId(string $synSetId): ?SynSet
    {
        if (array_key_exists($synSetId, $this->synSetList)) {
            return $this->synSetList[$synSetId];
        }
        return null;
    }

    /**
     * Returns SynSet with the specified literal and sense index.
     *
     * @param string $literal SynSet literal
     * @param int $sense SynSet's corresponding sense index
     * @return SynSet|null SynSet with the specified literal and sense index
     */
    public function getSynSetWithLiteral(string $literal, int $sense): ?SynSet
    {
        if (array_key_exists($literal, $this->literalList)) {
            foreach ($this->literalList[$literal] as $current) {
                if ($current->getSense() == $sense) {
                    return $this->getSynSetWithId($current->getSynSetId());
                }
            }
        }
        return null;
    }

    /**
     * Returns the number of SynSets with a specified literal.
     *
     * @param string $literal literal to be searched in SynSets
     * @return int the number of SynSets with a specified literal
     */
    public function numberOfSynSetsWithLiteral(string $literal): int
    {
        if (array_key_exists($literal, $this->literalList)) {
            return count($this->literalList[$literal]);
        } else {
            return 0;
        }
    }

    /**
     * Returns a list of SynSets with a specified part of speech tag.
     *
     * @param Pos $pos part of speech tag to be searched in SynSets
     * @return array a list of SynSets with a specified part of speech tag
     */
    public function getSynSetsWithPartOfSpeech(Pos $pos): array
    {
        $result = [];
        foreach ($this->synSetList as $synSet) {
            if ($synSet->getPos() != null && $synSet->getPos() == $pos) {
                $result[] = $synSet;
            }
        }
        return $result;
    }

    /**
     * Returns a list of literals with a specified literal String.
     *
     * @param string $literal literal String to be searched in literal list
     * @return array a list of literals with a specified literal String
     */
    public function getLiteralsWithName(string $literal): array
    {
        if (array_key_exists($literal, $this->literalList)) {
            return $this->literalList[$literal];
        } else {
            return [];
        }
    }

    /**
     * Finds the SynSet with specified literal String and part of speech tag and adds to the given SynSet list.
     *
     * @param array $result  SynSet list to add the specified SynSet
     * @param string $literal literal String to be searched in literal list
     * @param Pos $pos     part of speech tag to be searched in SynSets
     */
    private function addSynSetsWithLiteralToList(array &$result, string $literal, Pos $pos): void
    {
        foreach ($this->literalList[$literal] as $current) {
            $synSet = $this->getSynSetWithId($current->getSynSetId());
            if ($synSet != null && $synSet->getPos() == $pos) {
                $result[] = $synSet;
            }
        }
    }

    /**
     * Finds SynSets with specified literal String and adds to the newly created SynSet list.
     *
     * @param string $literal literal String to be searched in literal list
     * @return array returns a list of SynSets with specified literal String
     */
    public function getSynSetsWithLiteral(string $literal): array
    {
        $result = [];
        if (array_key_exists($literal, $this->literalList)) {
            foreach ($this->literalList[$literal] as $current) {
                $synSet = $this->getSynSetWithId($current->getSynSetId());
                if ($synSet != null) {
                    $result[] = $synSet;
                }
            }
        }
        return $result;
    }

    /**
     * Finds literals with specified literal String and adds to the newly created literal String list. Ex: cleanest - clean
     *
     * @param string $literal literal String to be searched in literal list
     * @return array returns a list of literals with specified literal String
     */
    public function getLiteralsWithPossibleModifiedLiteral(string $literal): array
    {
        $result = [];
        $result[] = $literal;
        $wordWithoutLastOne = mb_substr($literal, 0, mb_strlen($literal) - 1);
        $wordWithoutLastTwo = mb_substr($literal, 0, mb_strlen($literal) - 2);
        $wordWithoutLastThree = mb_substr($literal, 0, mb_strlen($literal) - 3);
        if (array_key_exists($literal, $this->exceptionList)) {
            foreach ($this->exceptionList[$literal] as $exceptionalWord) {
                $result[] = $exceptionalWord->getRoot();
            }
        }
        if (str_ends_with($literal, "s") && array_key_exists($wordWithoutLastOne, $this->literalList)) {
            $result[] = $wordWithoutLastOne;
        }
        if ((str_ends_with($literal, "es") || str_ends_with($literal, "ed") || str_ends_with($literal, "er")) && array_key_exists($wordWithoutLastTwo, $this->literalList)) {
            $result[] = $wordWithoutLastTwo;
        }
        if (str_ends_with($literal, "ed") && array_key_exists($wordWithoutLastTwo . mb_substr($literal, mb_strlen($literal) - 3, 1), $this->literalList)) {
            $result[] = $wordWithoutLastTwo . mb_substr($literal, mb_strlen($literal) - 3, 1);
        }
        if ((str_ends_with($literal, "ed") || str_ends_with($literal, "er")) && array_key_exists($wordWithoutLastTwo . "e", $this->literalList)) {
            $result[] = $wordWithoutLastTwo . "e";
        }
        if ((str_ends_with($literal, "ing") || str_ends_with($literal, "est")) && array_key_exists($wordWithoutLastThree, $this->literalList)) {
            $result[] = $wordWithoutLastThree;
        }
        if (str_ends_with($literal, "ing") && array_key_exists($wordWithoutLastThree . mb_substr($literal, mb_strlen($literal) - 4, 1), $this->literalList)) {
            $result[] = $wordWithoutLastThree . mb_substr($literal, mb_strlen($literal) - 4, 1);
        }
        if ((str_ends_with($literal, "ing") || str_ends_with($literal, "est")) && array_key_exists($wordWithoutLastThree . "e", $this->literalList)) {
            $result[] = $wordWithoutLastThree . "e";
        }
        if (str_ends_with($literal, "ies") && array_key_exists($wordWithoutLastThree . "y", $this->literalList)) {
            $result[] = $wordWithoutLastThree . "y";
        }
        return $result;
    }

    /**
     * Finds SynSets with specified literal String and part of speech tag, then adds to the newly created SynSet list. Ex: cleanest - clean
     *
     * @param string $literal literal String to be searched in literal list
     * @param Pos $pos part of speech tag to be searched in SynSets
     * @return array returns a list of SynSets with specified literal String and part of speech tag
     */
    public function getSynSetsWithPossiblyModifiedLiteral(string $literal, Pos $pos): array
    {
        $result = [];
        $modifiedLiterals = $this->getLiteralsWithPossibleModifiedLiteral($literal);
        foreach ($modifiedLiterals as $modifiedLiteral) {
            if (array_key_exists($modifiedLiteral, $this->literalList)) {
                $this->addSynSetsWithLiteralToList($result, $modifiedLiteral, $pos);
            }
        }
        return $result;
    }

    /**
     * Adds the reverse relations to the SynSet.
     *
     * @param SynSet $synSet SynSet to add the reverse relations
     * @param SemanticRelation $semanticRelation relation whose reverse will be added
     */
    public function addReverseRelation(SynSet $synSet, SemanticRelation $semanticRelation): void
    {
        $otherSynSet = $this->getSynSetWithId($semanticRelation->getName());
        if ($otherSynSet != null && SemanticRelation::reverse($semanticRelation->getRelationType()) != null) {
            $otherRelation = new SemanticRelation($synSet->getId(), SemanticRelation::reverse($semanticRelation->getRelationType()));
            if (!$otherSynSet->containsRelation($otherRelation)) {
                $otherSynSet->addRelation($otherRelation);
            }
        }
    }

    /**
     * Removes the reverse relations from the SynSet.
     *
     * @param SynSet $synSet SynSet to remove the reverse relation
     * @param SemanticRelation $semanticRelation relation whose reverse will be removed
     */
    public function removeReverseRelation(SynSet $synSet, SemanticRelation $semanticRelation): void
    {
        $otherSynSet = $this->getSynSetWithId($semanticRelation->getName());
        if ($otherSynSet != null && SemanticRelation::reverse($semanticRelation->getRelationType()) != null) {
            $otherRelation = new SemanticRelation($synSet->getId(), SemanticRelation::reverse($semanticRelation->getRelationType()));
            if ($otherSynSet->containsRelation($otherRelation)) {
                $otherSynSet->removeRelation($otherRelation);
            }
        }
    }

    /**
     * Loops through the SynSet list and adds the possible reverse relations.
     */
    private function equalizeSemanticRelations(): void
    {
        foreach ($this->synSetList as $synSet) {
            for ($i = 0; $i < $synSet->relationSize(); $i++) {
                if ($synSet->getRelation($i) instanceof SemanticRelation) {
                    $relation = $synSet->getRelation($i);
                    $this->addReverseRelation($synSet, $relation);
                }
            }
        }
    }

    /**
     * Creates a list of literals with a specified word, or possible words corresponding to morphological parse.
     *
     * @param string $word literal String
     * @param MorphologicalParse $parse morphological parse to get possible words
     * @param MetamorphicParse $metaParse metamorphic parse to get possible words
     * @param FsmMorphologicalAnalyzer $fsm finite state machine morphological analyzer to be used at getting possible words
     * @return array a list of literal
     */
    public function constructLiterals(string $word, MorphologicalParse $parse, MetamorphicParse $metaParse, FsmMorphologicalAnalyzer $fsm): array
    {
        $result = [];
        if ($parse->size() > 0) {
            if (!$parse->isPunctuation() && !$parse->isCardinal() && !$parse->isReal()) {
                $possibleWords = $fsm->getPossibleWords($parse, $metaParse);
                foreach ($possibleWords as $possibleWord) {
                    $result[] = $this->getLiteralsWithName($possibleWord);
                }
            } else {
                $result[] = $this->getLiteralsWithName($word);
            }
        } else {
            $result[] = $this->getLiteralsWithName($word);
        }
        return $result;
    }

    /**
     * Creates a list of SynSets with a specified word, or possible words corresponding to morphological parse.
     *
     * @param string $word      literal String  to get SynSets with
     * @param MorphologicalParse $parse     morphological parse to get SynSets with proper literals
     * @param MetamorphicParse $metaParse metamorphic parse to get possible words
     * @param FsmMorphologicalAnalyzer $fsm       finite state machine morphological analyzer to be used at getting possible words
     * @return array a list of SynSets
     */
    public function constructSynSets(string $word, MorphologicalParse $parse, MetamorphicParse $metaParse, FsmMorphologicalAnalyzer $fsm): array
    {
        $result = [];
        if ($parse->size() > 0) {
            if ($parse->isProperNoun()) {
                $result[] = $this->getSynSetWithLiteral("(özel isim)", 1);
            }
            if ($parse->isTime()) {
                $result[] = $this->getSynSetWithLiteral("(zaman)", 1);
            }
            if ($parse->isDate()) {
                $result[] = $this->getSynSetWithLiteral("(tarih)", 1);
            }
            if ($parse->isHashTag()) {
                $result[] = $this->getSynSetWithLiteral("(hashtag)", 1);
            }
            if ($parse->isEmail()) {
                $result[] = $this->getSynSetWithLiteral("(eposta)", 1);
            }
            if ($parse->isOrdinal()) {
                $result[] = $this->getSynSetWithLiteral("(sayı sıra sıfatı)", 1);
            }
            if ($parse->isPercent()) {
                $result[] = $this->getSynSetWithLiteral("(yüzde)", 1);
            }
            if ($parse->isFraction()) {
                $result[] = $this->getSynSetWithLiteral("(kesir sayı)", 1);
            }
            if ($parse->isRange()) {
                $result[] = $this->getSynSetWithLiteral("(sayı aralığı)", 1);
            }
            if ($parse->isReal()) {
                $result[] = $this->getSynSetWithLiteral("(reel sayı)", 1);
            }
            if (!$parse->isPunctuation() && !$parse->isCardinal() && !$parse->isReal()) {
                $possibleWords = $fsm->getPossibleWords($parse, $metaParse);
                foreach ($possibleWords as $possibleWord) {
                    $synSets = $this->getSynSetsWithLiteral($possibleWord);
                    if (count($synSets) > 0) {
                        foreach ($synSets as $synSet) {
                            if ($synSet->getPos() != null && ($parse->getPos() == "NOUN" || $parse->getPos() == "ADVERB" || $parse->getPos() == "VERB" || $parse->getPos() == "ADJ" || $parse->getPos() == "CONJ")) {
                                if ($synSet->getPos() == Pos::NOUN) {
                                    if ($parse->getPos() == "NOUN" || $parse->getRootPos() == "NOUN") {
                                        $result[] = $synSet;
                                    }
                                } else {
                                    if ($synSet->getPos() == Pos::ADVERB) {
                                        if ($parse->getPos() == "ADVERB" || $parse->getRootPos() == "ADVERB") {
                                            $result[] = $synSet;
                                        }
                                    } else {
                                        if ($synSet->getPos() == Pos::VERB) {
                                            if ($parse->getPos() == "VERB" || $parse->getRootPos() == "VERB") {
                                                $result[] = $synSet;
                                            }
                                        } else {
                                            if ($synSet->getPos() == Pos::ADJECTIVE) {
                                                if ($parse->getPos() == "ADJ" || $parse->getRootPos() == "ADJ") {
                                                    $result[] = $synSet;
                                                }
                                            } else {
                                                if ($synSet->getPos() == Pos::CONJUNCTION) {
                                                    if ($parse->getPos() == "CONJ" || $parse->getRootPos() == "CONJ") {
                                                        $result[] = $synSet;
                                                    }
                                                } else {
                                                    $result[] = $synSet;
                                                }
                                            }
                                        }
                                    }
                                }
                            } else {
                                $result[] = $synSet;
                            }
                        }
                    }
                }
                if (count($result) == 0) {
                    foreach ($possibleWords as $possibleWord) {
                        $synSets = $this->getSynSetsWithLiteral($possibleWord);
                        $result[] = $synSets;
                    }
                }
            } else {
                $result[] = $this->getSynSetsWithLiteral($word);
            }
            if ($parse->isCardinal() && count($result) == 0) {
                $result[] = $this->getSynSetWithLiteral("(tam sayı)", 1);
            }
        } else {
            $result[] = $this->getSynSetsWithLiteral($word);
        }
        return $result;
    }

    /**
     * Returns a list of literals using 5 possible words gathered with the specified morphological parses and metamorphic parses.
     *
     * @param FsmMorphologicalAnalyzer $fsm finite state machine morphological analyzer to be used at getting possible words
     * @param MorphologicalParse $morphologicalParse1 morphological parse to get possible words
     * @param MetamorphicParse $metaParse1 metamorphic parse to get possible words
     * @param MorphologicalParse $morphologicalParse2 morphological parse to get possible words
     * @param MetamorphicParse $metaParse2 metamorphic parse to get possible words
     * @param MorphologicalParse|null $morphologicalParse3 morphological parse to get possible words
     * @param MetamorphicParse|null $metaParse3 metamorphic parse to get possible words
     * @param MorphologicalParse|null $morphologicalParse4 morphological parse to get possible words
     * @param MetamorphicParse|null $metaParse4 metamorphic parse to get possible words
     * @param MorphologicalParse|null $morphologicalParse5 morphological parse to get possible words
     * @param MetamorphicParse|null $metaParse5 metamorphic parse to get possible words
     * @return array a list of literals
     */
    public function constructIdiomLiterals(FsmMorphologicalAnalyzer $fsm,
                                           MorphologicalParse       $morphologicalParse1, MetamorphicParse $metaParse1,
                                           MorphologicalParse       $morphologicalParse2, MetamorphicParse $metaParse2,
                                           ?MorphologicalParse      $morphologicalParse3, ?MetamorphicParse $metaParse3,
                                           ?MorphologicalParse      $morphologicalParse4, ?MetamorphicParse $metaParse4,
                                           ?MorphologicalParse      $morphologicalParse5, ?MetamorphicParse $metaParse5): array
    {
        $result = [];
        $possibleWords1 = $fsm->getPossibleWords($morphologicalParse1, $metaParse1);
        $possibleWords2 = $fsm->getPossibleWords($morphologicalParse2, $metaParse2);
        if ($morphologicalParse3 != null) {
            $possibleWords3 = $fsm->getPossibleWords($morphologicalParse3, $metaParse3);
        }
        if ($morphologicalParse4 != null) {
            $possibleWords4 = $fsm->getPossibleWords($morphologicalParse4, $metaParse4);
        }
        if ($morphologicalParse5 != null) {
            $possibleWords5 = $fsm->getPossibleWords($morphologicalParse5, $metaParse5);
        }
        if ($morphologicalParse5 != null) {
            foreach ($possibleWords1 as $possibleWord1) {
                foreach ($possibleWords2 as $possibleWord2) {
                    foreach ($possibleWords3 as $possibleWord3) {
                        foreach ($possibleWords4 as $possibleWord4) {
                            foreach ($possibleWords5 as $possibleWord5) {
                                $result[] = $this->getLiteralsWithName($possibleWord1 . " " . $possibleWord2 .
                                    " " . $possibleWord3 . " " . $possibleWord4 . " " . $possibleWord5);
                            }
                        }
                    }
                }
            }
        } else {
            if ($morphologicalParse4 != null) {
                foreach ($possibleWords1 as $possibleWord1) {
                    foreach ($possibleWords2 as $possibleWord2) {
                        foreach ($possibleWords3 as $possibleWord3) {
                            foreach ($possibleWords4 as $possibleWord4) {
                                $result[] = $this->getLiteralsWithName($possibleWord1 . " " . $possibleWord2 .
                                    " " . $possibleWord3 . " " . $possibleWord4);
                            }
                        }
                    }
                }
            } else {
                if ($morphologicalParse3 != null) {
                    foreach ($possibleWords1 as $possibleWord1) {
                        foreach ($possibleWords2 as $possibleWord2) {
                            foreach ($possibleWords3 as $possibleWord3) {
                                $result[] = $this->getLiteralsWithName($possibleWord1 . " " . $possibleWord2 .
                                    " " . $possibleWord3);
                            }
                        }
                    }
                } else {
                    foreach ($possibleWords1 as $possibleWord1) {
                        foreach ($possibleWords2 as $possibleWord2) {
                            $result[] = $this->getLiteralsWithName($possibleWord1 . " " . $possibleWord2);
                        }
                    }
                }
            }
        }
        return $result;
    }

    /**
     * Returns a list of SynSets using 5 possible words gathered with the specified morphological parses and metamorphic parses.
     *
     * @param FsmMorphologicalAnalyzer $fsm finite state machine morphological analyzer to be used at getting possible words
     * @param MorphologicalParse $morphologicalParse1 morphological parse to get possible words
     * @param MetamorphicParse $metaParse1 metamorphic parse to get possible words
     * @param MorphologicalParse $morphologicalParse2 morphological parse to get possible words
     * @param MetamorphicParse $metaParse2 metamorphic parse to get possible words
     * @param MorphologicalParse|null $morphologicalParse3 morphological parse to get possible words
     * @param MetamorphicParse|null $metaParse3 metamorphic parse to get possible words
     * @param MorphologicalParse|null $morphologicalParse4 morphological parse to get possible words
     * @param MetamorphicParse|null $metaParse4 metamorphic parse to get possible words
     * @param MorphologicalParse|null $morphologicalParse5 morphological parse to get possible words
     * @param MetamorphicParse|null $metaParse5 metamorphic parse to get possible words
     * @return array a list of SynSets
     */
    public function constructIdiomSynSets(FsmMorphologicalAnalyzer $fsm,
                                          MorphologicalParse       $morphologicalParse1, MetamorphicParse $metaParse1,
                                          MorphologicalParse       $morphologicalParse2, MetamorphicParse $metaParse2,
                                          ?MorphologicalParse      $morphologicalParse3, ?MetamorphicParse $metaParse3,
                                          ?MorphologicalParse      $morphologicalParse4, ?MetamorphicParse $metaParse4,
                                          ?MorphologicalParse      $morphologicalParse5, ?MetamorphicParse $metaParse5): array
    {
        $result = [];
        $possibleWords1 = $fsm->getPossibleWords($morphologicalParse1, $metaParse1);
        $possibleWords2 = $fsm->getPossibleWords($morphologicalParse2, $metaParse2);
        if ($morphologicalParse3 != null) {
            $possibleWords3 = $fsm->getPossibleWords($morphologicalParse3, $metaParse3);
        }
        if ($morphologicalParse4 != null) {
            $possibleWords4 = $fsm->getPossibleWords($morphologicalParse4, $metaParse4);
        }
        if ($morphologicalParse5 != null) {
            $possibleWords5 = $fsm->getPossibleWords($morphologicalParse5, $metaParse5);
        }
        if ($morphologicalParse5 != null) {
            foreach ($possibleWords1 as $possibleWord1) {
                foreach ($possibleWords2 as $possibleWord2) {
                    foreach ($possibleWords3 as $possibleWord3) {
                        foreach ($possibleWords4 as $possibleWord4) {
                            foreach ($possibleWords5 as $possibleWord5) {
                                if ($this->numberOfSynSetsWithLiteral($possibleWord1 . " " . $possibleWord2 . " " .
                                        $possibleWord3 . " " . $possibleWord4 . " " . $possibleWord5) > 0) {
                                    $result[] = $this->getSynSetsWithLiteral($possibleWord1 . " " . $possibleWord2 .
                                        " " . $possibleWord3 . " " . $possibleWord4 . " " . $possibleWord5);
                                }
                            }
                        }
                    }
                }
            }
        } else {
            if ($morphologicalParse4 != null) {
                foreach ($possibleWords1 as $possibleWord1) {
                    foreach ($possibleWords2 as $possibleWord2) {
                        foreach ($possibleWords3 as $possibleWord3) {
                            foreach ($possibleWords4 as $possibleWord4) {
                                if ($this->numberOfSynSetsWithLiteral($possibleWord1 . " " . $possibleWord2 . " " .
                                        $possibleWord3 . " " . $possibleWord4) > 0) {
                                    $result[] = $this->getSynSetsWithLiteral($possibleWord1 . " " . $possibleWord2 .
                                        " " . $possibleWord3 . " " . $possibleWord4);
                                }
                            }
                        }
                    }
                }
            } else {
                if ($morphologicalParse3 != null) {
                    foreach ($possibleWords1 as $possibleWord1) {
                        foreach ($possibleWords2 as $possibleWord2) {
                            foreach ($possibleWords3 as $possibleWord3) {
                                if ($this->numberOfSynSetsWithLiteral($possibleWord1 . " " . $possibleWord2 . " " .
                                        $possibleWord3) > 0) {
                                    $result[] = $this->getSynSetsWithLiteral($possibleWord1 . " " . $possibleWord2 .
                                        " " . $possibleWord3);
                                }
                            }
                        }
                    }
                } else {
                    foreach ($possibleWords1 as $possibleWord1) {
                        foreach ($possibleWords2 as $possibleWord2) {
                            if ($this->numberOfSynSetsWithLiteral($possibleWord1 . " " . $possibleWord2) > 0) {
                                $result[] = $this->getSynSetsWithLiteral($possibleWord1 . " " . $possibleWord2);
                            }
                        }
                    }
                }
            }
        }
        return $result;
    }

    /**
     * Sorts definitions of SynSets in SynSet list according to their lengths.
     */
    public function sortDefinitions(): void
    {
        foreach ($this->getSynSetList() as $synSet) {
            $synSet->sortDefinitions();
        }
    }

    /**
     * Returns a list of SynSets with the interlingual relations of a specified SynSet ID.
     *
     * @param string $synSetId SynSet ID to be searched
     * @return array a list of SynSets with the interlingual relations of a specified SynSet ID
     */
    public function getInterlingual(string $synSetId): array
    {
        if (array_key_exists($synSetId, $this->interlingualList)) {
            return $this->interlingualList[$synSetId];
        } else {
            return [];
        }
    }

    /**
     * Returns the size of the SynSet list.
     *
     * @return int the size of the SynSet list
     */
    public function size(): int
    {
        return count($this->synSetList);
    }

    /**
     * Conduct common operations between similarity metrics.
     *
     * @param array $pathToRootOfSynSet1 first list of Strings
     * @param array $pathToRootOfSynSet2 second list of Strings
     * @return int path length
     */
    public function findPathLength(array $pathToRootOfSynSet1, array $pathToRootOfSynSet2): int
    {
        for ($i = 0; $i < count($pathToRootOfSynSet1); $i++) {
            $foundIndex = array_search($pathToRootOfSynSet1[$i], $pathToRootOfSynSet2);
            if ($foundIndex !== false) {
                // Index of two lists - 1 is equal to path length. If there is not path, return -1
                return $i + $foundIndex - 1;
            }
        }
        return -1;
    }

    /**
     * Returns the depth of path.
     *
     * @param array $pathToRootOfSynSet1 first list of Strings
     * @param array $pathToRootOfSynSet2 second list of Strings
     * @return int LCS depth
     */
    public function findLCSdepth(array $pathToRootOfSynSet1, array $pathToRootOfSynSet2): int
    {
        $temp = $this->findLCS($pathToRootOfSynSet1, $pathToRootOfSynSet2);
        if ($temp != null) {
            return $temp[1];
        }
        return -1;
    }

    /**
     * Returns the ID of LCS of path.
     *
     * @param array $pathToRootOfSynSet1 first list of Strings
     * @param array $pathToRootOfSynSet2 second list of Strings
     * @return string|null LCS ID
     */
    public function findLCSid(array $pathToRootOfSynSet1, array $pathToRootOfSynSet2): ?string
    {
        $temp = $this->findLCS($pathToRootOfSynSet1, $pathToRootOfSynSet2);
        if ($temp != null) {
            return $temp[0];
        }
        return null;
    }

    /**
     * Returns depth and ID of the LCS.
     *
     * @param array $pathToRootOfSynSet1 first list of Strings
     * @param array $pathToRootOfSynSet2 second list of Strings
     * @return array|null depth and ID of the LCS
     */
    public function findLCS(array $pathToRootOfSynSet1, array $pathToRootOfSynSet2): ?array
    {
        for ($i = 0; $i < count($pathToRootOfSynSet1); $i++) {
            $LCSid = $pathToRootOfSynSet1[$i];
            if (array_search($LCSid, $pathToRootOfSynSet2)) {
                return [$LCSid, count($pathToRootOfSynSet1) - $i + 1];
            }
        }
        return null;
    }

    /**
     * Finds the path to the root node of a SynSets.
     *
     * @param SynSet $synSet SynSet whose root path will be found
     * @return array list of String corresponding to nodes in the path
     */
    public function findPathToRoot(SynSet $synSet): array
    {
        $pathToRoot = [];
        while ($synSet != null) {
            if (array_search($synSet->getId(), $pathToRoot)) {
                break;
            }
            $pathToRoot[] = $synSet->getId();
            $synSet = $this->percolateUp($synSet);
        }
        return $pathToRoot;
    }

    /**
     * Finds the parent of a node. It does not move until the root, instead it goes one level up.
     *
     * @param SynSet $root SynSet whose parent will be find
     * @return SynSet|null parent SynSet
     */
    public function percolateUp(SynSet $root): ?SynSet
    {
        for ($i = 0; $i < $root->relationSize(); $i++) {
            $r = $root->getRelation($i);
            if ($r instanceof SemanticRelation) {
                if ($r->getRelationType() == SemanticRelationType::HYPERNYM || $r->getRelationType() == SemanticRelationType::INSTANCE_HYPERNYM) {
                    // return even if one hypernym is found.
                    return $this->getSynSetWithId($r->getName());
                }
            }
        }
        return null;
    }
}