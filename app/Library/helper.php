<?php

if (! function_exists('varDump')) {
    function varDump($var, $descr = '', bool $returnString = true)
    {
        if (is_null($var)) {
            $outputStr = 'NULL :' . (! empty($descr) ? $descr . ' : ' : '') . 'NULL';
            if ($returnString) {
                return $outputStr;
            }
            \Log::info($outputStr);

            return;
        }
        if (is_scalar($var)) {
            $outputStr = 'scalar => (' . gettype($var) . ') :' . (! empty($descr) ? $descr . ' : ' : '') . $var;
            if ($returnString) {
                return $outputStr;
            }
            \Log::info($outputStr);

            return;
        }
        if (is_array($var)) {
            $outputStr = '[]';
            if (isset($var[0])) {
                if (is_subclass_of($var[0], 'Illuminate\Database\Eloquent\Model')) {
                    $collectionClassBasename = is_string($var[0]) ? class_basename($var[0]) : '';
                    $outputStr               = ' Array(' . count(collect($var)->toArray()) . ' of ' . $collectionClassBasename . ') :' . (! empty($descr) ? $descr . ' : ' : '') . print_r(
                        collect($var)->toArray(),
                        true
                    );
                } else {
                    $outputStr = 'Array(' . count($var) . ') :' . (! empty($descr) ? $descr . ' : ' : '') . print_r(
                        $var,
                        true
                    );
                }
            } else {
                $outputStr = 'Array(' . count($var) . ') :' . (! empty($descr) ? $descr . ' : ' : '') . print_r(
                    $var,
                    true
                );
            }

            if ($returnString) {
                return $outputStr;
            }

            return;
        }

        if (class_basename($var) === 'Request' or class_basename($var) === 'LoginRequest') {
            $request     = request();
            $requestData = $request->all();
            $outputStr   = 'Request:' . (! empty($descr) ? $descr . ' : ' : '') . print_r(
                $requestData,
                true
            );
            if ($returnString) {
                return $outputStr;
            }
            \Log::info($outputStr);

            return;
        }

        if (class_basename($var) === 'LengthAwarePaginator' or class_basename($var) === 'Collection') {
            $collectionClassBasename = '';
            if (isset($var[0])) {
                $collectionClassBasename = is_string($var[0]) ? class_basename($var[0]) : '';
            }
            $outputStr = ' Collection(' . count($var->toArray()) . ' of ' . $collectionClassBasename . ') :' . (! empty($descr) ? $descr . ' : ' : '') . print_r(
                $var->toArray(),
                true
            );
            if ($returnString) {
                return $outputStr;
            }
            \Log::info($outputStr);

            return;
        }

        if (gettype($var) === 'object') {
            if (is_subclass_of($var, 'Illuminate\Database\Eloquent\Model')) {
                $outputStr = ' (Model Object of ' . get_class($var) . ') :' . (! empty($descr) ? $descr . ' : ' : '') . print_r($var/*->getAttributes()*/
                    ->toArray(),
                    true
                );
                if ($returnString) {
                    return $outputStr;
                }
                return;
            }
            $outputStr = ' (Object of ' . get_class($var) . ') :' . (! empty($descr) ? $descr . ' : ' : '') . print_r(
                (array)$var, true
            );
            if ($returnString) {
                return $outputStr;
            }
            \Log::info($outputStr);

            return;
        }
    }
} // if ( ! function_exists('varDump')) {
