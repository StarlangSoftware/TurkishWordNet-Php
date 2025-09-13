<?php

namespace olcaytaner\WordNet\Similarity;

use olcaytaner\WordNet\Similarity\ICSimilarity;
use olcaytaner\WordNet\SynSet;
use olcaytaner\WordNet\WordNet;

class Lin extends ICSimilarity
{

    /**
     * Class constructor to set the wordnet and the information content hash map.
     * @param WordNet $wordNet WordNet for which similarity metrics will be calculated.
     * @param array $informationContents Information content hash map.
     */
    public function __construct(WordNet $wordNet, array $informationContents)
    {
        parent::__construct($wordNet, $informationContents);
    }

    /**
     * Computes Lin wordnet similarity metric between two synsets.
     * @param SynSet $synSet1 First synset
     * @param SynSet $synSet2 Second synset
     * @return float Lin wordnet similarity metric between two synsets
     */
    function computeSimilarity(SynSet $synSet1, SynSet $synSet2): float
    {
        $pathToRootOfSynSet1 = $this->wordNet->findPathToRoot($synSet1);
        $pathToRootOfSynSet2 = $this->wordNet->findPathToRoot($synSet2);
        $LCSid = $this->wordNet->findLCSid($pathToRootOfSynSet1, $pathToRootOfSynSet2);
        return (2 * $this->informationContents[$LCSid]) / ($this->informationContents[$synSet1->getId()] + $this->informationContents[$synSet2->getId()]);
    }
}