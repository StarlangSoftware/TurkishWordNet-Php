<?php

namespace olcaytaner\WordNet\Similarity;

use olcaytaner\WordNet\SynSet;
use olcaytaner\WordNet\WordNet;

abstract class Similarity
{
    protected WordNet $wordNet;

    abstract function computeSimilarity(SynSet $synSet1, SynSet $synSet2): float;
    public function __construct(WordNet $wordNet){
        $this->wordNet = $wordNet;
    }
}