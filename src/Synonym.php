<?php

namespace olcaytaner\WordNet;

class Synonym
{
    private array $literals = [];

    /**
     * A constructor that creates a new {@link Array} literals.
     */
    public function __construct(){
    }

    /**
     * Appends the specified Literal to the end of literals list.
     *
     * @param Literal $literal element to be appended to the list
     */
    public function addLiteral(Literal $literal): void{
        $this->literals[] = $literal;
    }

    /**
     * Moves the specified literal to the first of literals list.
     *
     * @param Literal $literal element to be moved to the first element of the list
     */
    public function moveFirst(Literal $literal): void{
        if ($this->contains($literal)){
            array_splice($this->literals, array_search($literal, $this->literals), 1);
            array_splice($this->literals, 0, 0, $literal);
        }
    }

    /**
     * Extracts literal groups as synonym lists and returns them as an array list. Each literal group consists of
     * literals with the same group number except 0 which represents single literals. For example let say 'ab', 'âb',
     * 'su' are 3 literals in the same synset, this method will return for that synset two synonyms: 'ab' and 'âb' are
     * in one synonym and 'su' is in another synonym.
     * @return array Array list of literal groups represented as synonyms
     */
    public function getUniqueLiterals(): array{
        $literalGroups = [];
        $groupNo = -1;
        $synonym = new Synonym();
        foreach ($this->literals as $literal){
            if ($literal->getGroupNo() != $groupNo){
                if ($groupNo != -1){
                    $literalGroups[] = $synonym;
                }
                $groupNo = $literal->getGroupNo();
                $synonym = new Synonym();
            } else {
                if ($groupNo == 0){
                    $literalGroups[] = $synonym;
                    $synonym = new Synonym();
                }
            }
            $synonym->addLiteral($literal);
        }
        $literalGroups[] = $synonym;
        return $literalGroups;
    }

    /**
     * Returns the element at the specified position in literals list.
     *
     * @param mixed $index index of the element to return
     * @return Literal|null the element at the specified position in the list
     */
    public function getLiteral(mixed $index): ?Literal{
        if (is_numeric($index)){
            return $this->literals[$index];
        } else {
            foreach ($this->literals as $literal){
                if ($literal->getName() == $index){
                    return $literal;
                }
            }
            return null;
        }
    }

    /**
     * Returns size of literals list.
     *
     * @return int the size of the list
     */
    public function literalSize(): int{
        return count($this->literals);
    }

    /**
     * Returns true if literals list contains the specified literal.
     *
     * @param Literal $literal element whose presence in the list is to be tested
     * @return bool true if the list contains the specified element
     */
    public function contains(Literal $literal): bool{
        return in_array($literal, $this->literals);
    }

    /**
     * Returns true if literals list contains the specified String literal.
     *
     * @param string $literalName element whose presence in the list is to be tested
     * @return bool true if the list contains the specified element
     */
    public function containsLiteral(string $literalName): bool{
        foreach ($this->literals as $literal){
            if ($literal->getName() == $literalName){
                return true;
            }
        }
        return false;
    }

    /**
     * Removes the first occurrence of the specified element from literals list,
     * if it is present. If the list does not contain the element, it stays unchanged.
     *
     * @param Literal $toBeRemoved element to be removed from the list, if present
     */
    public function removeLiteral(Literal $toBeRemoved): void{
        array_splice($this->literals, array_search($toBeRemoved, $this->literals), 1);
    }

    /**
     * Overridden toString method to print literals.
     *
     * @return string concatenated literals
     */
    public function __toString(): string{
        $result = "";
        foreach ($this->literals as $literal){
            $result .= $literal->getName() . " ";
        }
        return $result;
    }
}