<?php

namespace olcaytaner\WordNet;

class Relation
{
    protected string $name;

    /**
     * A constructor that sets the name of the relation.
     *
     * @param string $name String relation name
     */
    public function __construct(string $name){
        $this->name = $name;
    }

    /**
     * Accessor method for the relation name.
     *
     * @return String relation name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Mutator for the relation name.
     *
     * @param string $name String relation name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

}