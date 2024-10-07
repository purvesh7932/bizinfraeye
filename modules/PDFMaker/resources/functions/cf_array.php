<?php

/**
 * This function returns
 *
 * @param $name - array name
 * @param $value -
 *
 * */
if (!function_exists('addToCFArray')) {

    function addToCFArray($name, $value)
    {
        global $PDFContent;

        $PDFContent->PDFMakerCFArray[$name][] = $value;

        return "";
    }

}
/**
 * Join array elements with a string
 *
 * @param $name - array name
 * @param $glue -
 *
 * */
if (!function_exists('implodeCFArray')) {

    function implodeCFArray($name, $glue)
    {
        global $PDFContent;

        return implode($glue, $PDFContent->PDFMakerCFArray[$name]);
    }

}
/**
 * This function returns
 *
 * @param $name - array name
 * @param $value -
 *
 * */
if (!function_exists('addToCFArrayALL')) {

    function addToCFArrayALL($name, $value)
    {
        global $focus;

        $focus->PDFMakerCFArrayALL[$name][] = $value;

        return "";
    }

}
/**
 * Join array elements with a string
 *
 * @param $name - array name
 * @param $glue -
 *
 * */
if (!function_exists('implodeCFArrayALL')) {

    function implodeCFArrayALL($name, $glue)
    {
        global $focus;

        return implode($glue, $focus->PDFMakerCFArrayALL[$name]);
    }

}
/**
 * This function returns the sum of values in an array.
 *
 * @param $name - array name
 *
 * */
if (!function_exists('sumCFArray')) {

    function sumCFArray($name)
    {
        global $PDFContent;

        foreach ($PDFContent->PDFMakerCFArray[$name] as $key => $number) {
            $PDFContent->PDFMakerCFArray[$name][$key] = its4you_formatNumberFromPDF($number);
        }

        $value = array_sum($PDFContent->PDFMakerCFArray[$name]);

        return its4you_formatNumberToPDF($value);
    }

}

if (!function_exists('sumCFArrayInt')) {

    /**
     * @param $name
     * @return int
     */
    function sumCFArrayInt($name)
    {
        global $PDFContent;

        foreach ($PDFContent->PDFMakerCFArray[$name] as $key => $number) {
            $PDFContent->PDFMakerCFArray[$name][$key] = its4you_formatNumberFromPDF($number);
        }

        $value = array_sum($PDFContent->PDFMakerCFArray[$name]);

        return (int) its4you_formatNumberToPDF($value);
    }

}

if (!function_exists('sumCFArrayRound')) {

    /**
     * @param $name
     * @param $roundNum
     * @return int|float
     */
    function sumCFArrayRound($name, $roundNum)
    {
        global $PDFContent;

        foreach ($PDFContent->PDFMakerCFArray[$name] as $key => $number) {
            $PDFContent->PDFMakerCFArray[$name][$key] = its4you_formatNumberFromPDF($number);
        }

        $value = array_sum($PDFContent->PDFMakerCFArray[$name]);
        $roundNum = $roundNum ? $roundNum : 0;

        return round(its4you_formatNumberToPDF($value), $roundNum);
    }

}

/**
 * This function returns the sum of values in an array.
 *
 * @param $name - array name
 *
 * */
if (!function_exists('sumCFArrayAll')) {

    function sumCFArrayAll($name)
    {
        global $focus;
        foreach ($focus->PDFMakerCFArrayALL[$name] as $key => $number) {
            $focus->PDFMakerCFArrayALL[$name][$key] = its4you_formatNumberFromPDF($number);
        }

        $value = array_sum($focus->PDFMakerCFArrayALL[$name]);

        return its4you_formatNumberToPDF($value);
    }

}

/**
 * @param string $name
 * @param string $value
 * @param string $inArrayReturn
 * @param string $notInArrayReturn
 * @return string
 */

if (!function_exists('inCFArray')) {

    function inCFArray($name, $value, $inArrayReturn = '', $notInArrayReturn = '')
    {
        global $PDFContent;

        return in_array($value, (array)$PDFContent->PDFMakerCFArray[$name]) ? $inArrayReturn : $notInArrayReturn;
    }

}