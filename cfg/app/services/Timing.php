<?php

namespace cfg\app\services;

class Timing {

    function format($date, $prefix = FALSE, $sortie_jour = FALSE, $time = FALSE) {
        if ($date) {
            $tab_date = array(
                'mois' => array(
                    'complet' => array(
                        1 => 'janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet',
                        'août', 'septembre', 'octobre', 'novembre', 'décembre'),
                    'prefix' => array(
                        1 => 'jan', 'fev', 'mar', 'avr', 'mai', 'jui', 'juil', 'aou', 'sep',
                        'oct', 'nov', 'déc'
                    )
                ),
                'jour' => array(
                    'complet' => array(
                        'dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'
                    ),
                    'prefix' => array(
                        'dim', 'lun', 'mar', 'mer', 'jeu', 'ven', 'sam'
                    )
                )
            );

            //si la date est renversée
            if (preg_match("#^[\d]{4}(-[\d]{2}){2}( (.+))?$#", $date)) {

                $dates = explode(' ', $date);
                $date_date = $dates[0];
                $date_time = isset($dates[1]) ? $dates[1] : FALSE;

                $date_date_format = explode('-', $date_date);
                $date_date_year = $date_date_format[0];
                $date_date_month = $date_date_format[1];
                $date_date_day = $date_date_format[2];
            } else {

                $dates = explode(' ', $date);
                $date_date = $dates[0];
                $date_time = isset($dates[1]) ? $dates[1] : FALSE;

                $date_date_format = explode('-', $date_date);
                $date_date_year = $date_date_format[2];
                $date_date_month = $date_date_format[1];
                $date_date_day = $date_date_format[0];
            }

            $gregorian_date = gregoriantojd($date_date_month, $date_date_day, $date_date_year);
            $day_of_week = jddayofweek($gregorian_date, 0);

            $format = ($prefix) ? 'prefix' : 'complet';
            $jour = (preg_match("#^0#", $day_of_week)) ? substr($day_of_week, 1) : $day_of_week;
            $mois = (preg_match("#^0#", $date_date_month)) ? substr($date_date_month, 1) : $date_date_month;

            if (preg_match("#^[0]{4}(-[0]{2}){2}( (.+))?$#", $date) || preg_match("#^([0]{2}-){2}[0]{4}( (.+))?$#", $date)) {

                $out = '-';
                
            } else {

                if ($sortie_jour) {
                    if (date('Y') == $date_date_year) {
                        if ($date_time && $time) {
                            $out = $tab_date['jour'][$format][$jour] . " {$date_date_day} " . $tab_date['mois'][$format][$mois] . " &agrave; {$date_time}";
                        } else {
                            $out = $tab_date['jour'][$format][$jour] . " {$date_date_day} " . $tab_date['mois'][$format][$mois];
                        }
                    } else {
                        if ($date_time && $time) {
                            $out = $tab_date['jour'][$format][$jour] . " {$date_date_day} " . $tab_date['mois'][$format][$mois] . " {$date_date_year} &agrave; {$date_time}";
                        } else {
                            $out = $tab_date['jour'][$format][$jour] . " {$date_date_day} " . $tab_date['mois'][$format][$mois] . " {$date_date_year}";
                        }
                    }
                } else {
                    if (date('Y') == $date_date_year) {
                        if ($date_time && $time) {
                            $out = $date_date_day . " " . $tab_date['mois'][$format][$mois] . " &agrave; {$date_time}";
                        } else {
                            $out = $date_date_day . " " . $tab_date['mois'][$format][$mois];
                        }
                    } else {
                        if ($date_time && $time) {
                            $out = $date_date_day . " " . $tab_date['mois'][$format][$mois] . " {$date_date_year} &agrave; {$date_time}";
                        } else {
                            $out = $date_date_day . " " . $tab_date['mois'][$format][$mois] . " {$date_date_year}";
                        }
                    }
                }
            }
        } else {
            $out = NULL;
        }

        return $out;
    }

}

?>