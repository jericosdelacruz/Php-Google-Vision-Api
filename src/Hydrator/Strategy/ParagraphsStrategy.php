<?php

namespace Vision\Hydrator\Strategy;

use Vision\Annotation\Paragraph;
use Zend\Hydrator\Strategy\StrategyInterface;

class ParagraphsStrategy implements StrategyInterface
{
    /**
     * @var TextPropertyStrategy
     */
    protected $textPropertyStrategy;

    /**
     * @var BoundingPolyStrategy
     */
    protected $boundingPolyStrategy;

    /**
     * @var WordsStrategy
     */
    protected $wordsStrategy;

    public function __construct()
    {
        $this->textPropertyStrategy = new TextPropertyStrategy;
        $this->boundingPolyStrategy = new BoundingPolyStrategy;
        $this->wordsStrategy = new WordsStrategy;
    }

    /**
     * @param Paragraph[] $value
     * @return array
     */
    public function extract($value)
    {
        return array_map(function(Paragraph $paragraphEntity) {
            $textProperty = $paragraphEntity->getProperty()
                ? $this->textPropertyStrategy->extract($paragraphEntity->getProperty())
                : null;

            $boundingBox = $paragraphEntity->getBoundingBox()
                ? $this->boundingPolyStrategy->extract($paragraphEntity->getBoundingBox())
                : null;

            $words = $paragraphEntity->getWords()
                ? $this->wordsStrategy->extract($paragraphEntity->getWords())
                : null;


            return array_filter([
                'property' => $textProperty,
                'boundingBox' => $boundingBox,
                'words' => $words,
            ]);
        }, $value);
    }

    /**
     * @param array $value
     * @return Paragraph[]
     */
    public function hydrate($value)
    {
        $paragraphEntities = [];
        foreach ($value as $paragraphEntityInfo) {
            $textProperty = isset($paragraphEntityInfo['property'])
                ? $this->textPropertyStrategy->hydrate($paragraphEntityInfo['property'])
                : null;

            $boundingBox = isset($paragraphEntityInfo['boundingBox'])
                ? $this->boundingPolyStrategy->hydrate($paragraphEntityInfo['boundingBox'])
                : null;

            $words = isset($paragraphEntityInfo['words'])
                ? $this->wordsStrategy->hydrate($paragraphEntityInfo['words'])
                : null;

            $paragraphEntities[] = new Paragraph($textProperty, $boundingBox, $words);
        }

        return $paragraphEntities;
    }
}
