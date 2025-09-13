<?php

namespace olcaytaner\WordNet;

use olcaytaner\Dictionary\Dictionary\Pos;

class SynSet
{
    private string $id;
    private ?Pos $pos = null;
    private ?array $definition = null;
    private ?string $example = null;
    private Synonym $synonym;
    private array $relations = [];
    private string $note;
    private ?string $wikiPage = null;
    private int $bcs;

    /**
     * Constructor initialize SynSet ID, synonym and relations list.
     *
     * @param string $id Synset ID
     */
    public function __construct(string $id){
        $this->id = $id;
        $this->synonym = new Synonym();
    }

    /**
     * Accessor for the SynSet ID.
     *
     * @return string SynSet ID
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Mutator method for the SynSet ID.
     *
     * @param string $id SynSet ID to be set
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * Mutator method for the definition.
     *
     * @param string $definition String definition
     */
    public function setDefinition(string $definition): void{
        $this->definition = explode("|", $definition);
    }

    /**
     * Removes the specified definition from long definition.
     *
     * @param string $definition definition to be removed
     */
    public function removeDefinition(string $definition): void{
        $longDefinition = $this->getLongDefinition();
        if (str_starts_with($longDefinition, $definition . "|")) {
            $this->setDefinition(str_replace($longDefinition, $definition . "|", ""));
        } else {
            if (str_ends_with($longDefinition, "|" . $definition)) {
                $this->setDefinition(str_replace($longDefinition, "|" . $definition, ""));
            } else {
                if (str_contains($longDefinition, "|" . $definition . "|")) {
                    $this->setDefinition(str_replace($longDefinition, "|" . $definition, ""));
                }
            }
        }
    }

    /**
     * Returns the first literal's name.
     *
     * @return string the first literal's name.
     */
    public function representative(): string{
        return $this->getSynonym()->getLiteral(0);
    }

    /**
     * Returns all the definitions in the list.
     *
     * @return string|null all the definitions
     */
    public function getLongDefinition(): ?string{
        if ($this->definition !== null) {
            $longDefinition = $this->definition[0];
            for ($i = 1; $i < count($this->definition); $i++) {
                $longDefinition .= "|" . $this->definition[$i];
            }
            return $longDefinition;
        } else {
            return null;
        }
    }

    /**
     * Sorts definitions list according to their lengths.
     */
    public function sortDefinitions(): void{
        if ($this->definition !== null) {
            for ($i = 0; $i < count($this->definition); $i++) {
                for ($j = $i + 1; $j < count($this->definition[$i]); $j++) {
                    $tmp = $this->definition[$i];
                    $this->definition[$i] = $this->definition[$j];
                    $this->definition[$j] = $tmp;
                }
            }
        }
    }

    /**
     * Accessor for the definition at specified index.
     *
     * @param int|null $index definition index to be accessed
     * @return string|null definition at specified index
     */
    public function getDefinition(?int $index = null): ?string{
        if ($index === null) {
            if ($this->definition !== null) {
                return $this->definition[0];
            } else {
                return null;
            }
        } else {
            if ($index < count($this->definition) && $index >= 0) {
                return $this->definition[$index];
            } else {
                return null;
            }
        }
    }

    /**
     * Returns number of definitions in the list.
     *
     * @return int number of definitions in the list.
     */
    public function numberOfDefinitions(): int{
        if ($this->definition !== null) {
            return count($this->definition);
        } else {
            return 0;
        }
    }

    /**
     * Accessor for the example.
     *
     * @return string|null String example
     */
    public function getExample(): ?string
    {
        return $this->example;
    }

    /**
     * Mutator for the example.
     *
     * @param string $example String that will be used to set
     */
    public function setExample(string $example): void
    {
        $this->example = $example;
    }

    /**
     * Accessor for the bcs value
     *
     * @return int bcs value
     */
    public function getBcs(): int
    {
        return $this->bcs;
    }

    /**
     * Mutator for the bcs value which enables the connection with the BalkaNet.
     *
     * @param int $bcs bcs value
     */
    public function setBcs(int $bcs): void
    {
        $this->bcs = $bcs;
    }

    /**
     * Accessor for the part of speech tag.
     *
     * @return Pos part of speech tag
     */
    public function getPos(): ?Pos
    {
        return $this->pos;
    }

    /**
     * Mutator for the part of speech tags.
     *
     * @param Pos $pos part of speech tag
     */
    public function setPos(Pos $pos): void
    {
        $this->pos = $pos;
    }

    /**
     * Accessor for the available notes.
     *
     * @return string String note
     */
    public function getNote(): string
    {
        return $this->note;
    }

    /**
     * Mutator for the available notes.
     *
     * @param string $note String note to be set
     */
    public function setNote(string $note): void
    {
        $this->note = $note;
    }

    /**
     * Accessor for the synonym.
     *
     * @return Synonym synonym
     */
    public function getSynonym(): Synonym
    {
        return $this->synonym;
    }

    /**
     * Accessor for the wiki page.
     *
     * @return string|null String wiki page
     */
    public function getWikiPage(): ?string
    {
        return $this->wikiPage;
    }

    /**
     * Mutator for the wiki pages.
     *
     * @param string|null $wikiPage String wiki page
     */
    public function setWikiPage(?string $wikiPage): void
    {
        $this->wikiPage = $wikiPage;
    }

    /**
     * Appends the specified Relation to the end of relations list.
     *
     * @param Relation $relation element to be appended to the list
     */
    public function addRelation(Relation $relation): void{
        $this->relations[] = $relation;
    }

    /**
     * Removes the first occurrence of the specified element from relations list,
     * if it is present. If the list does not contain the element, it stays unchanged.
     *
     * @param mixed $relation element to be removed from the list, if present
     */
    public function removeRelation(mixed $relation): void{
        if ($relation instanceof Relation) {
            array_splice($this->relations, array_search($relation, $this->relations, true), 1);
        } else {
            if (is_string($relation)) {
                for ($i = 0; $i < count($this->relations); $i++) {
                    if ($relation == $this->relations[$i]->getName()) {
                        array_splice($this->relations, $i, 1);
                        break;
                    }
                }
            }
        }
    }

    /**
     * Returns the element at the specified position in relations list.
     *
     * @param int $index index of the element to return
     * @return Relation the element at the specified position in the list
     */
    public function getRelation(int $index): Relation{
        return $this->relations[$index];
    }

    /**
     * Returns the size of the relations list.
     *
     * @return int the size of the relations list
     */
    public function relationSize(): int{
        return count($this->relations);
    }

    /**
     * Adds a specified literal to the synonym.
     *
     * @param Literal $literal literal to be added
     */
    public function addLiteral(Literal $literal): void{
        $this->synonym->addLiteral($literal);
    }

    /**
     * Compares literals of synonym and the specified SynSet, returns true if their have same literals.
     *
     * @param SynSet $synSet SynSet to compare
     * @return bool true if SynSets have same literals, false otherwise
     */
    public function containsSameLiteral(SynSet $synSet): bool{
        for ($i = 0; $i < $this->synonym->literalSize(); $i++) {
            $literal1 = $this->synonym->getLiteral($i)->getName();
            for ($j = 0; $j < $this->synonym->literalSize(); $j++) {
                $literal2 = $this->synonym->getLiteral($j)->getName();
                if ($literal1 === $literal2) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Returns true if relations list contains the specified relation.
     *
     * @param Relation $relation element whose presence in the list is to be tested
     * @return bool true if the list contains the specified element
     */
    public function containsRelation(Relation $relation): bool{
        return in_array($relation, $this->relations);
    }

    /**
     * Returns true if specified semantic relation type presents in the relations list.
     *
     * @param SemanticRelationType $relationType
     * @return bool true if specified semantic relation type presents in the relations list
     */
    public function containsRelationType(SemanticRelationType $relationType): bool{
        foreach ($this->relations as $relation) {
            if ($relation instanceof SemanticRelation && $relation->getRelationType() === $relationType) {
                return true;
            }
        }
        return false;
    }

    /**
     * Merges synonym and a specified SynSet with their definitions, relations, part of speech tags and examples.
     *
     * @param SynSet $synSet SynSet to be merged
     */
    public function mergeSynSet(SynSet $synSet): void{
        for ($i = 0; $i < $this->synonym->literalSize(); $i++) {
            if (!$this->synonym->contains($synSet->getSynonym()->getLiteral($i))){
                $this->synonym->addLiteral($synSet->getSynonym()->getLiteral($i));
            }
        }
        if ($this->definition == null && $synSet->getDefinition() != null) {
            $this->setDefinition($synSet->getDefinition());
        } else {
            if ($this->definition != null && $synSet->getDefinition() != null && $this->getLongDefinition() != $synSet->getLongDefinition()) {
                $this->setDefinition($this->getLongDefinition() . "|" . $synSet->getLongDefinition());
            }
        }
        if ($synSet->relationSize() != 0){
            for ($i = 0; $i < $synSet->relationSize(); $i++) {
                if (!$this->containsRelation($synSet->getRelation($i)) && $synSet->getRelation($i)->getName() != $this->getId()){
                    $this->addRelation($synSet->getRelation($i));
                }
            }
        }
        if ($this->pos == null && $synSet->getPos() != null){
            $this->setPos($synSet->getPos());
        }
        if ($this->example == null && $synSet->getExample() != null){
            $this->example = $synSet->getExample();
        }
    }

    /**
     * Overridden toString method to print the first definition or representative.
     *
     * @return string print the first definition or representative.
     */
    public function __toString(): string{
        if ($this->definition != null){
            return $this->definition[0];
        } else {
            return $this->representative();
        }
    }

}