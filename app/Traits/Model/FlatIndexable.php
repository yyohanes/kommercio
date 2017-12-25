<?php

namespace Kommercio\Traits\Model;

use Illuminate\Support\Facades\DB;

/**
 * Trait to save model to flat index
 */
trait FlatIndexable {
    /**
     * Get table name used for flat indexing
     * @return string|null
     */
    public function getFlatTable() {
        return $this->flatTable;
    }

    /**
     * Check if this model can be flat-indexed
     * @return boolean
     */
    public function isFlatIndexable() {
        return property_exists($this, 'flatTable');
    }

    /**
     * Get value by key
     * @return mixed
     */
    private function getFlatIndexValueByKey($key) {
        $keys = explode('.', $key);

        if (count($keys) > 1) {
            if (!preg_match('/\(\)/', $keys[0])) {
                $middleValue = $this->{$keys[0]};
            } else {
                $methodName = str_replace('()', '', $keys[0]);
                $middleValue = $this->$methodName();
            }

            if ($middleValue) {
                // If has `fillDetails` method, it's a Profile
                if (method_exists($middleValue, 'fillDetails')) {
                    $middleValue->fillDetails();
                }

                return $middleValue->{$keys[1]};
            }

            return null;
        } else {
            if (preg_match('/\(\)/', $key)) {
                $methodName = str_replace('()', '', $key);
                
                return $this->$methodName();
            }

            return $this->{$key};
        }
    }

    /**
     * Get key
     * @return string
     */
    private function getFlatIndexKey($key) {
        $key = str_replace('.', '_', $key);
        $key = str_replace('()', '', $key);

        return $key;
    }

    /**
     * Save model to flat index
     * @return boolean
     */
    public function saveFlatIndex() {
        if ($this->isFlatIndexable()
                && property_exists($this, 'flatIndexables')
                && is_array($this->flatIndexables)) {
            
            $values = [
                'id' => $this->id,
            ];
            foreach ($this->flatIndexables as $index) {
                $key = $this->getFlatIndexKey($index);
                $value = $this->getFlatIndexValueByKey($index);
                $values[$key] = $value ? : null;
            }

            // Try to save as new, if error code is `1062` we update instead
            try {
                DB::table($this->flatTable)->insert($values);
                
                return true;
            } catch (\Exception $e) {
                $errorCode = $errorCode = $e->errorInfo[1];

                if ($errorCode == 1062) {
                    try {
                        DB::table($this->flatTable)
                        ->where('id', '=', $this->id)
                        ->update($values);

                        return true;
                    } catch (\Exception $e2) {
                        \Log::error($e2->getMessage());
                    }
                } else {
                    \Log::error($e->getMessage());
                }
            }

            return false;
        }
    }

    /**
     * Delete model from flat index
     * @return boolean
     */
    public function deleteFlatIndex() {
        if ($this->isFlatIndexable()) {
            try {
                DB::table($this->flatTable)
                    ->where('id', '=', $this->id)
                    ->delete();
                
                return true;
            } catch (\Exception $e) {
                \Log::error($e->getMessage());
            }
        }

        return false;
    }

    /**
     * Find from flat index
     * @return int|null
     */
    public static function flatFindBy($property, $value) {
        $model = with(new self());

        if ($model->isFlatIndexable()) {
            $row = DB::table($model->flatTable)
                ->where($property, '=', $value)
                ->first();

            $rowId = $row ? $row->id : null;

            if ($rowId) {
                return self::find($rowId);
            }
        }

        return null;
    }
}
