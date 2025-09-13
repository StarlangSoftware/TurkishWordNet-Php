<?php

namespace olcaytaner\WordNet;

enum InterlingualDependencyType
{
    case HYPERNYM;
    case NEAR_ANTONYM;
    case HOLO_MEMBER;
    case HOLO_PART;
    case HOLO_PORTION;
    case USAGE_DOMAIN;
    case CATEGORY_DOMAIN;
    case BE_IN_STATE;
    case SUBEVENT;
    case VERB_GROUP;
    case SIMILAR_TO;
    case ALSO_SEE;
    case CAUSES;
    case SYNONYM;
}