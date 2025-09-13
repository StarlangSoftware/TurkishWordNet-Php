<?php

namespace olcaytaner\WordNet;

class SemanticRelation extends Relation
{
    private ?SemanticRelationType $relationType;
    private int $toIndex = 0;

    private static array $semanticDependency = ["ANTONYM", "HYPERNYM",
        "INSTANCE_HYPERNYM", "HYPONYM", "INSTANCE_HYPONYM", "MEMBER_HOLONYM", "SUBSTANCE_HOLONYM",
        "PART_HOLONYM", "MEMBER_MERONYM", "SUBSTANCE_MERONYM", "PART_MERONYM", "ATTRIBUTE",
        "DERIVATION_RELATED", "DOMAIN_TOPIC", "MEMBER_TOPIC", "DOMAIN_REGION", "MEMBER_REGION",
        "DOMAIN_USAGE", "MEMBER_USAGE", "ENTAILMENT", "CAUSE", "ALSO_SEE",
        "VERB_GROUP", "SIMILAR_TO", "PARTICIPLE_OF_VERB"];

    private static array $semanticDependencyTags = [SemanticRelationType::ANTONYM, SemanticRelationType::HYPERNYM,
        SemanticRelationType::INSTANCE_HYPERNYM, SemanticRelationType::HYPONYM, SemanticRelationType::INSTANCE_HYPONYM, SemanticRelationType::MEMBER_HOLONYM, SemanticRelationType::SUBSTANCE_HOLONYM,
        SemanticRelationType::PART_HOLONYM, SemanticRelationType::MEMBER_MERONYM, SemanticRelationType::SUBSTANCE_MERONYM, SemanticRelationType::PART_MERONYM, SemanticRelationType::ATTRIBUTE,
        SemanticRelationType::DERIVATION_RELATED, SemanticRelationType::DOMAIN_TOPIC, SemanticRelationType::MEMBER_TOPIC, SemanticRelationType::DOMAIN_REGION, SemanticRelationType::MEMBER_REGION,
        SemanticRelationType::DOMAIN_USAGE, SemanticRelationType::MEMBER_USAGE, SemanticRelationType::ENTAILMENT, SemanticRelationType::CAUSE, SemanticRelationType::ALSO_SEE,
        SemanticRelationType::VERB_GROUP, SemanticRelationType::SIMILAR_TO, SemanticRelationType::PARTICIPLE_OF_VERB];

    /**
     * Another constructor that initializes relation type, relation name, and the index.
     *
     * @param string $name         name of the relation
     * @param mixed $relationType String semantic dependency tag
     * @param ?int $toIndex      index of the relation
     */
    public function __construct(string $name, mixed $relationType, ?int $toIndex = null){
        parent::__construct($name);
        if (is_string($relationType)){
            $this->relationType = self::getSemanticTag($relationType);
        } else {
            $this->relationType = $relationType;
        }
        if ($toIndex !== null){
            $this->toIndex = $toIndex;
        }
    }

    /**
     * Accessor to retrieve semantic relation type given a specific semantic dependency tag.
     *
     * @param string $tag String semantic dependency tag
     * @return ?SemanticRelationType semantic relation type
     */
    public static function getSemanticTag(string $tag): ?SemanticRelationType{
        for ($j = 0; $j < count(self::$semanticDependencyTags); $j++){
            if (strtoupper($tag) === strtoupper(self::$semanticDependency[$j])){
                return self::$semanticDependencyTags[$j];
            }
        }
        return null;
    }

    /**
     * Returns the reverse of a specific semantic relation type.
     *
     * @param SemanticRelationType $semanticRelationType semantic relation type to be reversed
     * @return SemanticRelationType|null reversed version of the semantic relation type
     */
    public static function reverse(SemanticRelationType $semanticRelationType): ?SemanticRelationType{
        switch ($semanticRelationType){
            case SemanticRelationType::HYPERNYM:
                return SemanticRelationType::HYPONYM;
            case SemanticRelationType::HYPONYM:
                return SemanticRelationType::HYPERNYM;
            case SemanticRelationType::ANTONYM:
                return SemanticRelationType::ANTONYM;
            case SemanticRelationType::INSTANCE_HYPERNYM:
                return SemanticRelationType::INSTANCE_HYPONYM;
            case SemanticRelationType::INSTANCE_HYPONYM:
                return SemanticRelationType::INSTANCE_HYPERNYM;
            case SemanticRelationType::MEMBER_HOLONYM:
                return SemanticRelationType::MEMBER_MERONYM;
            case SemanticRelationType::MEMBER_MERONYM:
                return SemanticRelationType::MEMBER_HOLONYM;
            case SemanticRelationType::PART_MERONYM:
                return SemanticRelationType::PART_HOLONYM;
            case SemanticRelationType::PART_HOLONYM:
                return SemanticRelationType::PART_MERONYM;
            case SemanticRelationType::SUBSTANCE_MERONYM:
                return SemanticRelationType::SUBSTANCE_HOLONYM;
            case SemanticRelationType::SUBSTANCE_HOLONYM:
                return SemanticRelationType::SUBSTANCE_MERONYM;
            case SemanticRelationType::DOMAIN_TOPIC:
                return SemanticRelationType::MEMBER_TOPIC;
            case SemanticRelationType::MEMBER_TOPIC:
                return SemanticRelationType::DOMAIN_TOPIC;
            case SemanticRelationType::DOMAIN_REGION:
                return SemanticRelationType::MEMBER_REGION;
            case SemanticRelationType::MEMBER_REGION:
                return SemanticRelationType::DOMAIN_REGION;
            case SemanticRelationType::DOMAIN_USAGE:
                return SemanticRelationType::MEMBER_USAGE;
            case SemanticRelationType::MEMBER_USAGE:
                return SemanticRelationType::DOMAIN_USAGE;
            case SemanticRelationType::DERIVATION_RELATED:
                return SemanticRelationType::DERIVATION_RELATED;
        }
        return null;
    }

    /**
     * Accessor for the semantic relation type.
     *
     * @return SemanticRelationType|null semantic relation type
     */
    public function getRelationType(): ?SemanticRelationType
    {
        return $this->relationType;
    }

    /**
     * Mutator for the semantic relation type.
     *
     * @param SemanticRelationType|null $relationType semantic relation type.
     */
    public function setRelationType(?SemanticRelationType $relationType): void
    {
        $this->relationType = $relationType;
    }

    /**
     * Returns the index value.
     *
     * @return int index value.
     */
    public function toIndex(): int
    {
        return $this->toIndex;
    }

    /**
     * Accessor method to retrieve the semantic relation type as a String.
     *
     * @return string String semantic relation type
     */
    public function getTypeAsString(): string{
        if ($this->relationType !== null){
            for ($j = 0; $j < count(self::$semanticDependencyTags); $j++){
                if (self::$semanticDependencyTags[$j] === $this->relationType){
                    return self::$semanticDependency[$j];
                }
            }
        } else {
            return "";
        }
        return "";
    }

    /**
     * Overridden toString method to print semantic relation types and names.
     *
     * @return string semantic relation types and names
     */
    public function __toString(): string{
        return $this->getTypeAsString() . "->" . $this->getName();
    }
}