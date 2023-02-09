<?php

namespace SW_WAPF\Includes\Classes {

    use Exception;
    use ArrayIterator;

    class Enumerable
    {
        private $iterator;

        private function __construct (ArrayIterator $iterator)
        {
            $this->iterator = $iterator;
            $this->iterator->rewind();
        }

        public static function from ($source)
        {
            $iterator = null;

            if ($source instanceof Enumerable)
                return $source;
            if (is_array($source))
                $iterator = new ArrayIterator($source);

            if ($iterator !== null)
            {
                return new Enumerable($iterator);
            }

            return new Enumerable(new ArrayIterator([]));
        }

        #region Query functions

        public function select($predicate)
        {
            $this->iterator->rewind();

            $objects = [];

            while ($this->iterator->valid())
            {
                array_push($objects,$predicate($this->iterator->current(), $this->iterator->key()));
                $this->iterator->next();
            }
            return self::from($objects);
        }

        public function where ($predicate)
        {
            $this->iterator->rewind();

            $keys = [];
            while ($this->iterator->valid())
            {
                if(!$predicate($this->iterator->current(), $this->iterator->key()))
                    array_push($keys, $this->iterator->key());
                $this->iterator->next();
            }

            foreach($keys as $key){
                $this->iterator->offsetUnset($key);
            }

            return $this;
        }

        public function firstOrDefault($predicate)
        {

            $this->iterator->rewind();
            if(!$this->iterator->valid()) return null;

            while ($this->iterator->valid())
            {
                if($predicate($this->iterator->current(), $this->iterator->key()))
                    return $this->iterator->current();
                $this->iterator->next();
            }

            return null;
        }

        public function orderByDesc($predicate){

            $comparer = function($a,$b)use($predicate){
                if($predicate($a) === $predicate($b) )
                    return 0;
                return ($predicate($a) < $predicate($b)) ? 1 : -1;
            };

            $this->iterator->uasort($comparer);
            return $this;
        }

        public function orderBy($predicate) {

            $comparer = function($a,$b)use($predicate){
                if($predicate($a) === $predicate($b) )
                    return 0;
                return ($predicate($a) < $predicate($b)) ? -1 : 1;
            };

            $this->iterator->uasort($comparer);
            return $this;
        }

        #endregion

        #region Boolean Functions
        public function any($predicate = null)
        {
            if($predicate === null)
                return iterator_count($this->iterator) > 0;

            return $this->firstOrDefault($predicate) != null;
        }

        #endregion

        #region Integer Functions
        public function count($predicate = null)
        {
            if($predicate === null)
                return iterator_count($this->iterator);
            return iterator_count($this->where($predicate)->iterator);
        }
        #endregion

        #region String Functions

        public function join($value_predicate, $separator)
        {
            $this->iterator->rewind();

            $result = [];
            while ($this->iterator->valid())
            {
                array_push($result, $value_predicate($this->iterator->current(),$this->iterator->key()));
                $this->iterator->next();
            }

            return join($separator, $result);
        }

        #endregion

        #region Operations
        public function flatten()
        {
            $flat = [];

            $this->iterator->rewind();
            while ($this->iterator->valid())
            {
                if(is_array($this->iterator->current())){
                    foreach($this->iterator->current() as $e){
                        array_push($flat,$e);
                    }
                }
                $this->iterator->next();
            }

            return self::from($flat);
        }



        public function merge($predicate) {

            $merged = [];

            $this->iterator->rewind();
            while ($this->iterator->valid())
            {
                $value = $predicate($this->iterator->current(),$this->iterator->key());
                $merged = array_merge($merged, is_array($value) ? $value : [$value]);
                $this->iterator->next();
            }

            return self::from($merged);

        }
        #endregion

        #region Conversion Functions
        public function toArray()
        {
            $this->iterator->rewind();

            if ($this->iterator instanceof ArrayIterator)
                return $this->iterator->getArrayCopy();

            $result = [];
            foreach ($this->iterator as $k => $v) {
                $result[ $k ] = $v;
            }
            return $result;
        }
        #endregion

    }
}