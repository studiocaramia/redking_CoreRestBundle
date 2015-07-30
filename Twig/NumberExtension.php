<?php

namespace Redking\Bundle\CoreRestBundle\Twig;

class NumberExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('bytes', array($this, 'bytesFilter')),
        );
    }

    /**
     * Retourne la valeur décimale d'un nombre de bytes sous forme human readable
     * @param  [type]  $bytes                 Valeur a convertir
     * @param  boolean $binary                Affiche la valeur avec le diviseur binaire 1024
     * @param  boolean $redisplay             Re-affiche la valeur sans formatage (boolean optionnel)
     * @param  array   $number_format_options Options pour number_format
     * @return string
     */
    public function bytesFilter($bytes, $binary = true, $redisplay = true, $number_format_options = [] )
    {
        $number_format_default_options = [
            'decimals'      => 2, 
            'dec_point'     => ",", 
            'thousands_sep' => " "
        ];
        $number_format_options = array_merge($number_format_default_options, $number_format_options);

        if ($binary === true) {
            $multiplier  = 1024;
            // $unit_suffix = 'io';
            $unit_suffix = 'o';
        } else  {
            $multiplier  = 1000;
            $unit_suffix = 'o';
        }

        $kilobyte = $multiplier;
        $megabyte = $kilobyte * $multiplier;
        $gigabyte = $megabyte * $multiplier;
        $terabyte = $gigabyte * $multiplier;
        $petabyte = $terabyte * $multiplier;

        if ($bytes < $kilobyte) {
            $number = $bytes;
            $units = '';
            if ($bytes == 0) {
                $number_format_options['decimals'] = 0;
                $redisplay = false;
                $unit_suffix = '';
            }
        } elseif ($bytes < $megabyte) {
            $number = $bytes / $kilobyte;
            $units = 'K';
        } elseif ($bytes < $gigabyte) {
            $number = $bytes / $megabyte;
            $units = 'M';
        } elseif ($bytes < $terabyte) {
            $number = $bytes / $gigabyte;
            $units = 'G';
        } elseif ($bytes < $kilobyte) {
            $number = $bytes / $terabyte;
            $units = 'T';
        } else {
            $number = $bytes / $petabyte;
            $units = 'P';
        }
        
        $redisplay_raw_units = ($redisplay == true) ? ' ('.number_format($bytes, 0, $number_format_options['dec_point'], $number_format_options['thousands_sep']).')' : '';
        
        return number_format($number, $number_format_options['decimals'], $number_format_options['dec_point'], $number_format_options['thousands_sep']) . ' ' . $units.$unit_suffix.$redisplay_raw_units;
    }

    public function getName()
    {
        return 'redking_core_rest_number_extension';
    }
}
