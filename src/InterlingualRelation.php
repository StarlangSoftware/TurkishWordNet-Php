<?php

namespace olcaytaner\WordNet;

class InterlingualRelation extends Relation
{
    private InterlingualDependencyType $dependencyType;

    private static array $ilrDependency = ["Hypernym", "Near_antonym", "Holo_member", "Holo_part", "Holo_portion",
        "Usage_domain", "Category_domain", "Be_in_state", "Subevent", "Verb_group",
        "Similar_to", "Also_see", "Causes", "SYNONYM"];

    private static array $interlingualDependencyTags = [InterlingualDependencyType::HYPERNYM,
        InterlingualDependencyType::NEAR_ANTONYM, InterlingualDependencyType::HOLO_MEMBER, InterlingualDependencyType::HOLO_PART,
        InterlingualDependencyType::HOLO_PORTION, InterlingualDependencyType::USAGE_DOMAIN, InterlingualDependencyType::CATEGORY_DOMAIN,
        InterlingualDependencyType::BE_IN_STATE, InterlingualDependencyType::SUBEVENT, InterlingualDependencyType::VERB_GROUP,
        InterlingualDependencyType::SIMILAR_TO, InterlingualDependencyType::ALSO_SEE, InterlingualDependencyType::CAUSES,
        InterlingualDependencyType::SYNONYM];

    /**
     * InterlingualRelation method sets its relation with the specified String name, then gets the InterlingualDependencyType
     * according to specified String dependencyType.
     *
     * @param string $name           relation name
     * @param string $dependencyType interlingual dependency type
     */
    public function __construct(string $name, string $dependencyType){
        parent::__construct($name);
        $this->dependencyType = InterlingualRelation::getInterlingualDependencyTag($dependencyType);
    }

    /**
     * Compares specified {@code String} tag with the tags in InterlingualDependencyType {@code Array}, ignoring case
     * considerations.
     *
     * @param string $tag String to compare
     * @return InterlingualDependencyType|null interlingual dependency type according to specified tag
     */
    public static function getInterlingualDependencyTag(string $tag): ?InterlingualDependencyType{
        for ($j = 0; $j < count(self::$ilrDependency); $j++){
            if (strtoupper($tag) === strtoupper(self::$ilrDependency[$j])){
                return self::$interlingualDependencyTags[$j];
            }
        }
        return null;
    }

    /**
     * Accessor method to get the private InterlingualDependencyType.
     *
     * @return InterlingualDependencyType interlingual dependency type
     */
    public function getType(): InterlingualDependencyType{
        return $this->dependencyType;
    }

    /**
     * Method to retrieve interlingual dependency type as {@code String}.
     *
     * @return string interlingual dependency type
     */
    public function getTypeAsString(): string{
        for ($j = 0; $j < count(self::$ilrDependency); $j++){
            if (self::$interlingualDependencyTags[$j] === $this->dependencyType){
                return self::$ilrDependency[$j];
            }
        }
        return "";
    }

    /**
     * toString method to print interlingual dependency type.
     *
     * @return string String of relation name
     */
    public function __toString(): string{
        return $this->getTypeAsString() . "->" . $this->getName();
    }
}