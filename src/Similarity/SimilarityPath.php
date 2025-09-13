<?php

namespace olcaytaner\WordNet\Similarity;

use olcaytaner\WordNet\Similarity\Similarity;
use olcaytaner\WordNet\SynSet;
use olcaytaner\WordNet\WordNet;

class SimilarityPath extends Similarity
{

    /**
     * Class constructor that sets the wordnet and the information content hash map.
     * @param WordNet $wordNet WordNet for which similarity metrics will be calculated.
     */
    public function __construct(WordNet $wordNet)
    {
        parent::__construct($wordNet);
    }

    /**
     * Computes wordnet similarity metric based on similarity path between two synsets.
     * @param SynSet $synSet1 First synset
     * @param SynSet $synSet2 Second synset
     * @return float Wordnet similarity metric based on similarity path between two synsets.
     */
    function computeSimilarity(SynSet $synSet1, SynSet $synSet2): float
    {
        $pathToRootOfSynSet1 = $this->wordNet->findPathToRoot($synSet1);
        $pathToRootOfSynSet2 = $this->wordNet->findPathToRoot($synSet2);
        $pathLength = $this->wordNet->findPathLength($pathToRootOfSynSet1, $pathToRootOfSynSet2);
        $maxDepth = max(count($pathToRootOfSynSet1), count($pathToRootOfSynSet2));
        return 2 * $maxDepth - $pathLength;
    }
}