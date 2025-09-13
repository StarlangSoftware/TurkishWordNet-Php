<?php

namespace olcaytaner\WordNet;

class Literal
{
    protected string $name;
    protected int $sense;
    protected string $synSetId;
    protected ?string $origin = null;
    protected array $relations = [];
    protected int $groupNo;

    /**
     * A constructor that initializes name, sense, SynSet ID and the relations.
     *
     * @param string $name     name of a literal
     * @param int $sense    index of sense
     * @param string $synSetId ID of the SynSet
     */
    public function __construct(string $name, int $sense, string $synSetId){
        $this->name = $name;
        $this->sense = $sense;
        $this->synSetId = $synSetId;
        $this->groupNo = 0;
    }

    /**
     * Accessor method to return name of the literal.
     *
     * @return string name of the literal
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Accessor method to return the index of sense of the literal.
     *
     * @return int index of sense of the literal
     */
    public function getSense(): int
    {
        return $this->sense;
    }

    /**
     * Accessor method to return SynSet ID.
     *
     * @return string String of SynSet ID
     */
    public function getSynSetId(): string
    {
        return $this->synSetId;
    }

    /**
     * Accessor method to return the origin of the literal.
     *
     * @return string|null origin of the literal
     */
    public function getOrigin(): ?string
    {
        return $this->origin;
    }

    /**
     * Mutator method to set the origin with specified origin.
     *
     * @param string $origin origin of the literal to set
     */
    public function setOrigin(string $origin): void
    {
        $this->origin = $origin;
    }

    /**
     * Mutator method to set the group no with specified group no.
     *
     * @param int $groupNo group no of the literal to set
     */
    public function setGroupNo(int $groupNo): void
    {
        $this->groupNo = $groupNo;
    }

    /**
     * Mutator method to set the sense index of the literal.
     *
     * @param int $sense sense index of the literal to set
     */
    public function setSense(int $sense): void
    {
        $this->sense = $sense;
    }

    /**
     * Mutator method to set name of a literal.
     *
     * @param string $name name of the literal to set
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * Mutator method to set SynSet ID of a literal.
     *
     * @param string $synSetId SynSet ID of the literal to set
     */
    public function setSynSetId(string $synSetId): void
    {
        $this->synSetId = $synSetId;
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
     * @param Relation $relation element to be removed from the list, if present
     */
    public function removeRelation(Relation $relation): void{
        $this->relations = array_splice($this->relations, array_search($relation, $this->relations, true), 1);
    }

    /**
     * Returns true if relations list contains the specified relation.
     *
     * @param Relation $relation element whose presence in the list is to be tested
     * @return bool true if the list contains the specified element
     */
    public function containsRelation(Relation $relation): bool{
        return in_array($relation, $this->relations, true);
    }

    /**
     * Returns true if specified semantic relation type presents in the relations list.
     *
     * @param SemanticRelationType $relationType element whose presence in the list is to be tested
     * @return bool true if specified semantic relation type presents in the relations list
     */
    public function containsRelationType(SemanticRelationType $relationType): bool{
        foreach ($this->relations as $relation){
            if ($relation instanceof SematicRelation && $relation->getRelationType() == $relationType){
                return true;
            }
        }
        return false;
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
     * Returns size of relations list.
     *
     * @return int the size of the list
     */
    public function relationSize(): int{
        return count($this->relations);
    }

    public function getGroupNo(): int
    {
        return $this->groupNo;
    }

    /**
     * Overridden toString method to print names and sense of literals.
     *
     * @return string concatenated names and senses of literals
     */
    public function __toString(): string{
        return $this->name . $this->sense;
    }
}