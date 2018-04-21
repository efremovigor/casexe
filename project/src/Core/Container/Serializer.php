<?php

namespace Core\Container;

use Core\Container\Entity\PropertyAccessInterface;


/**
 * todo tothink is really need so complex and magic class
 * Class Serializer
 * @package Helpers
 */
class Serializer
{

    /**
     * nullable - Обнулять параметрами из источника
     * rewritable - Перезаписывать параметрами из источника
     * addable - Добавлять параметрами источника, если субьект имеет, что-то у себя
     */
    public const ADDABLE    = 'addable';
    public const REWRITABLE = 'rewritable';
    public const NULLABLE   = 'nullable';

    /**
     * Десериализует данные
     * @param $source
     * @param $subject
     * @param array $params
     * @return mixed
     */
    public function normalize($source, $subject = null, array $params = [self::ADDABLE])
    {
        switch(true) {
            case \is_object($subject):
                switch(true) {
                    /**
                     * object -> object
                     */
                    case \is_object($source) && $source instanceOf PropertyAccessInterface:
                        $this->objectToObject($source, $subject,$params);
                        break;
                    /**
                     * array -> object
                     */
                    case \is_array($source):
                        $this->arrayToObject($source,$subject,$params);
                        break;
                    /**
                     * json -> object
                     */
                    case $this->is_json($source):
                        $this->normalize(json_decode($source, true), $subject,$params);
                        break;
                }
                break;
            /**
             * Создает класс по имени и рекурсивно вызываем
             */
            case \is_string($subject):
                if(class_exists($subject)) {
                    $subject = $this->normalize($source, new $subject(),$params);
                }
                break;
            case \is_array($subject) || $subject === null:
                switch(true) {
                    /**
                     * object -> array
                     */
                    case \is_object($source) && $source instanceOf PropertyAccessInterface:
                        $this->objectToArray($source,$subject,$params);
                        break;
                    /**
                     * array -> array
                     */
                    case \is_array($source):
                        foreach($source as $key => $element) {
                            /**
                             * Если элемент массива - массив, и он определен в субьекте - то лезем внутрь
                             */
                            if(\is_array($element) && isset($subject[$key])) {
                                $subject[$key] = $this->normalize($element, $subject[$key],$params);
                            } else {
                                $subject[$key] = $element;
                            }
                        }
                        break;
                    /**
                     * array -> json
                     */
                    case !\is_object($source) && $this->is_json($source):
                        $subject = json_decode($source, true);
                        break;
                }
                break;
        }


        return $subject;
    }

    /**
     * @param $source
     * @param string $type
     * @return mixed
     */
    public function serialize($source, string $type = 'json')
    {
        switch(true) {
            case \is_object($source) && $source instanceOf PropertyAccessInterface:
                $source = $this->normalize($source);
            case \is_array($source):
                if($type === 'json') {
                    $source = json_encode($source, true);
                }
                break;
        }

        return $source;
    }


    /**
     * Переливает обьект в обьект
     * @param PropertyAccessInterface $source
     * @param object $subject
     * @param array $params
     * @return void
     */
    private function objectToObject(PropertyAccessInterface $source, object &$subject, array $params = []): void
    {
        foreach($source->getProperties() as $property) {
            $setMethod = $this->setMethod($property);
            $addMethod = $this->addMethod($property);
            $getMethod = $this->getMethod($property);
            /**
             * Добавляет элементы если свойство в объекте - это массив
             */
            if(method_exists($source, $getMethod) &&
                \is_array($source->$getMethod()) &&
                method_exists($subject, $addMethod)
            ) {
                if($this->isAddable($params) === false && \count($source->$getMethod()) > 0) {
                    continue;
                }
                foreach($source->$getMethod() as $subValue) {
                    $subject->$addMethod($subValue);
                }
                continue;
            }
            /**
             * Рекурсивно вызывается,если св-во subject является обьектом
             */
            if(method_exists($source, $getMethod) &&
                method_exists($subject, $getMethod) &&
                \is_object($subject->$getMethod()) &&
                (\is_array($source->$getMethod()) || \is_object($source->$getMethod()))
            ) {
                $this->normalize($source->$getMethod(), $subject->$getMethod());
                continue;
            }
            /**
             * Простой сет свойства, если они совпадают по имени
             */
            if(method_exists($subject, $setMethod)) {
                if($this->isNullable($params) === false && $source->$getMethod() === null) {
                    continue;
                }
                if($this->isRewritable($params) === false && $subject->$getMethod() !== null) {
                    continue;
                }
                $subject->$setMethod($source->$getMethod());
                continue;
            }
        }
    }

    private function arrayToObject(array $source, object &$subject, array $params = [])
    {
        foreach($source as $key => $value) {
            $setMethod = $this->setMethod($key);
            $addMethod = $this->addMethod($key);
            $getMethod = $this->getMethod($key);
            /**
             */
            if(\is_array($value) && method_exists($subject, $addMethod)) {
                if($this->isAddable($params) === false && \count($value) > 0) {
                    continue;
                }
                foreach($value as $subValue) {
                    $subject->$addMethod($subValue);
                }
                continue;
            }
            /**
             * Рекурсивно вызывается,если св-во subject является обьектом
             */
            if((\is_array($value) || \is_object($value)) &&
                method_exists($subject, $getMethod) &&
                \is_object($subject->$getMethod())
            ) {
                $this->normalize($value, $subject->$getMethod($value));
                continue;
            }
            /**
             * Простой сет свойства, если они совпадают по имени
             */
            if(method_exists($subject, $setMethod)) {
                if($this->isNullable($params) === false && $value === null) {
                    continue;
                }
                if($this->isRewritable($params) === false && $subject->$getMethod() !== null) {
                    continue;
                }
                $subject->$setMethod($value);
                continue;
            }
        }
    }

    private function objectToArray(PropertyAccessInterface $source,array &$subject = [], array $params = []){
        foreach($source->getProperties() as $property) {
            if(!array_key_exists($property, $subject)) {
                $subject[$property] = null;
            }
            $getMethod = $this->getMethod($property);

            if(\is_array($source->$getMethod())) {

                if(!empty($subject[$property])) {
                    if($this->isAddable($params) === false && \count($source->$getMethod()) > 0) {
                        continue;
                    }
                    $subject[$property] = array_merge($source->$getMethod(), $subject[$property]);
                } else {
                    $subject[$property] = $source->$getMethod();
                }
                continue;
            }
            /**
             * Рекурсивно вызывается,если св-во subject является обьектом
             */
            if(method_exists($source, $getMethod) &&
                (\is_array($source->$getMethod()) || \is_object($source->$getMethod()))
            ) {
                $subject[$property] = $this->normalize($source->$getMethod(), $subject[$property]);
                continue;
            }
            /**
             * Простой сет свойства, если они совпадают по имени
             */
            if($this->isNullable($params) === false && $source->$getMethod() === null) {
                continue;
            }
            if($this->isRewritable($params) === false && $subject[$property] !== null) {
                continue;
            }
            $subject[$property] = $source->$getMethod();
            continue;
        }
    }

    /**
     * @param $key
     *
     * @return string
     */
    protected function setMethod(string $key): string
    {
        return 'set' . ucfirst($key);
    }

    /**
     * @param $key
     *
     * @return string
     */
    protected function getMethod(string $key): string
    {
        return 'get' . ucfirst($key);
    }

    /**
     * @param $key
     *
     * @return string
     */
    protected function addMethod(string $key): string
    {
        return 'add' . ucfirst($key);
    }

    /**
     * @param $data
     * @return bool
     */
    private function is_json($data): bool
    {
        json_decode($data);

        return (json_last_error() === JSON_ERROR_NONE);
    }

    /**
     * @param array $params
     * @return bool
     */
    private function isNullable(array $params): bool
    {
        return array_key_exists(static::NULLABLE, $params);
    }

    /**
     * @param array $params
     * @return bool
     */
    private function isRewritable(array $params): bool
    {
        return array_key_exists(static::REWRITABLE, $params);
    }

    /**
     * @param array $params
     * @return bool
     */
    private function isAddable(array $params): bool
    {
        return array_key_exists(static::ADDABLE, $params);
    }
}