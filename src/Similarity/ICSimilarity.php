<?php

namespace olcaytaner\WordNet\Similarity;

use olcaytaner\WordNet\WordNet;

abstract class ICSimilarity extends Similarity
{
    protected array $informationContents = [];

    /**
     * Abstract class constructor to set the wordnet and the information content hash map.
     * @param WordNet $wordNet WordNet for which similarity metrics will be calculated.
     * @param array $informationContents Information content hash map.
     */
    public function __construct(WordNet $wordNet, array $informationContents){
        parent::__construct($wordNet);
        $this->informationContents = $informationContents;
    }
}