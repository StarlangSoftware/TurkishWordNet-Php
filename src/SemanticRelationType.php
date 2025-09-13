<?php

namespace olcaytaner\WordNet;

enum SemanticRelationType
{
    case ANTONYM;
    case HYPERNYM;
    case INSTANCE_HYPERNYM;
    case HYPONYM;
    case INSTANCE_HYPONYM;
    case MEMBER_HOLONYM;
    case SUBSTANCE_HOLONYM;
    case PART_HOLONYM;
    case MEMBER_MERONYM;
    case SUBSTANCE_MERONYM;
    case PART_MERONYM;
    case ATTRIBUTE;
    case DERIVATION_RELATED;
    case DOMAIN_TOPIC;
    case MEMBER_TOPIC;
    case DOMAIN_REGION;
    case MEMBER_REGION;
    case DOMAIN_USAGE;
    case MEMBER_USAGE;
    case ENTAILMENT;
    case CAUSE;
    case ALSO_SEE;
    case VERB_GROUP;
    case SIMILAR_TO;
    case PARTICIPLE_OF_VERB;
}