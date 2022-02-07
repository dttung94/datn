<?php
namespace common\helper;

class DatetimeHelper
{
    const
        FULL_DATETIME = 'Y-m-d H:i:s',
        FULL_DATE = 'Y-m-d',
        FULL_TIME = 'H:i:s',
        DEFAULT_TIMEZONE = 'UTC';

    public static function now($format = self::FULL_DATETIME)
    {
        return date($format, time());
    }

    public static function seconds2Datetime($seconds = null, $format = self::FULL_DATETIME)
    {
        $seconds = $seconds ? $seconds : time();
        return date($format, $seconds);
    }

    public static function getDayOfWeekFromDate($date)
    {
        return date('N', strtotime($date));
    }

    public static function timeFormat2Seconds($time, $separator = ":")
    {
        $data = explode($separator, $time);
        $seconds = 0;
        if (isset($data[2])) {
            $seconds += intval($data[2]);
        }
        if (isset($data[1])) {
            $seconds += intval($data[1]) * 60;
        }
        if (isset($data[0])) {
            $seconds += intval($data[0]) * 60 * 60;
        }
        return $seconds;
    }

    public static function getHourFromTimeFormat($time, $separator = ":", $default = null)
    {
        if ($time != null) {
            $data = explode($separator, $time);
            if (isset($data[0])) {
                return intval($data[0]) . "";
            }
        }
        return $default;
    }

    public static function getMinuteFromTimeFormat($time, $separator = ":", $default = null)
    {
        if ($time != null) {
            $data = explode($separator, $time);
            if (isset($data[1])) {
                return intval($data[1]) . "";
            }
        }
        return $default;
    }

    public static function getListHours($start = 0, $end = 24)
    {
        $start = $start < 0 ? 0 : $start;
        $end = $end > 24 ? 24 : $end;
        $hours = [];
        for ($i = $start; $i <= $end; $i++) {
            $hours[$i] = "$i giờ";
        }
        return $hours;
    }

    public static function getListMinutes($step = 5)
    {
        $minutes = [];
        for ($i = 0; $i < 60; $i += $step) {
            $minutes[$i] = "$i phút";
        }
        return $minutes;
    }

    public static function getDayOfWeek($index, $language = null, $isFull = true)
    {
        $dayOfWeekData = [
            "ja" => [
                "prefix" => "",
                "suffixes" => "",
                "labels" => [
                    1 => "Mon",
                    2 => "Tue",
                    3 => "Wed",
                    4 => "Thu",
                    5 => "Fri",
                    6 => "Sat",
                    7 => "Sun",
                ]
            ],
        ];
        $language = $language == null ? \App::$app->language : $language;
        if (isset($dayOfWeekData[$language])) {
            $value = $dayOfWeekData[$language]["labels"][$index];
            if ($isFull) {
                $value = $dayOfWeekData[$language]["prefix"] . $value . $dayOfWeekData[$language]["suffixes"];
            }
            return $value;
        }
        return null;
    }

    /**
     * TODO get list timezone
     * @return array
     */
    public static function getListTimezone()
    {
        return [
            "Pacific/Midway" => "(GMT-11:00) Midway Island, Samoa",
            "America/Adak" => "(GMT-10:00) Hawaii-Aleutian",
            "Etc/GMT+10" => "(GMT-10:00) Hawaii",
            "Pacific/Marquesas" => "(GMT-09:30) Marquesas Islands",
            "Pacific/Gambier" => "(GMT-09:00) Gambier Islands",
            "America/Anchorage" => "(GMT-09:00) Alaska",
            "America/Ensenada" => "(GMT-08:00) Tijuana, Baja California",
            "Etc/GMT+8" => "(GMT-08:00) Pitcairn Islands",
            "America/Los_Angeles" => "(GMT-08:00) Pacific Time (US & Canada)",
            "America/Denver" => "(GMT-07:00) Mountain Time (US & Canada)",
            "America/Chihuahua" => "(GMT-07:00) Chihuahua, La Paz, Mazatlan",
            "America/Dawson_Creek" => "(GMT-07:00) Arizona",
            "America/Belize" => "(GMT-06:00) Saskatchewan, Central America",
            "America/Cancun" => "(GMT-06:00) Guadalajara, Mexico City, Monterrey",
            "Chile/EasterIsland" => "(GMT-06:00) Easter Island",
            "America/Chicago" => "(GMT-06:00) Central Time (US & Canada)",
            "America/New_York" => "(GMT-05:00) Eastern Time (US & Canada)",
            "America/Havana" => "(GMT-05:00) Cuba",
            "America/Bogota" => "(GMT-05:00) Bogota, Lima, Quito, Rio Branco",
            "America/Caracas" => "(GMT-04:30) Caracas",
            "America/Santiago" => "(GMT-04:00) Santiago",
            "America/La_Paz" => "(GMT-04:00) La Paz",
            "Atlantic/Stanley" => "(GMT-04:00) Faukland Islands",
            "America/Campo_Grande" => "(GMT-04:00) Brazil",
            "America/Goose_Bay" => "(GMT-04:00) Atlantic Time (Goose Bay)",
            "America/Glace_Bay" => "(GMT-04:00) Atlantic Time (Canada)",
            "America/St_Johns" => "(GMT-03:30) Newfoundland",
            "America/Araguaina" => "(GMT-03:00) UTC-3",
            "America/Montevideo" => "(GMT-03:00) Montevideo",
            "America/Miquelon" => "(GMT-03:00) Miquelon, St. Pierre",
            "America/Godthab" => "(GMT-03:00) Greenland",
            "America/Argentina/Buenos_Aires" => "(GMT-03:00) Buenos Aires",
            "America/Sao_Paulo" => "(GMT-03:00) Brasilia",
            "America/Noronha" => "(GMT-02:00) Mid-Atlantic",
            "Atlantic/Cape_Verde" => "(GMT-01:00) Cape Verde Is.",
            "Atlantic/Azores" => "(GMT-01:00) Azores",
            "Europe/Belfast" => "(GMT) Greenwich Mean Time : Belfast",
            "Europe/Dublin" => "(GMT) Greenwich Mean Time : Dublin",
            "Europe/Lisbon" => "(GMT) Greenwich Mean Time : Lisbon",
            "Europe/London" => "(GMT) Greenwich Mean Time : London",
            "Africa/Abidjan" => "(GMT) Monrovia, Reykjavik",
            "Europe/Amsterdam" => "(GMT+01:00) Amsterdam, Berlin, Bern, Rome, Stockholm, Vienna",
            "Europe/Belgrade" => "(GMT+01:00) Belgrade, Bratislava, Budapest, Ljubljana, Prague",
            "Europe/Brussels" => "(GMT+01:00) Brussels, Copenhagen, Madrid, Paris",
            "Africa/Algiers" => "(GMT+01:00) West Central Africa",
            "Africa/Windhoek" => "(GMT+01:00) Windhoek",
            "Asia/Beirut" => "(GMT+02:00) Beirut",
            "Africa/Cairo" => "(GMT+02:00) Cairo",
            "Asia/Gaza" => "(GMT+02:00) Gaza",
            "Africa/Blantyre" => "(GMT+02:00) Harare, Pretoria",
            "Asia/Jerusalem" => "(GMT+02:00) Jerusalem",
            "Europe/Minsk" => "(GMT+02:00) Minsk",
            "Asia/Damascus" => "(GMT+02:00) Syria",
            "Europe/Moscow" => "(GMT+03:00) Moscow, St. Petersburg, Volgograd",
            "Africa/Addis_Ababa" => "(GMT+03:00) Nairobi",
            "Asia/Tehran" => "(GMT+03:30) Tehran",
            "Asia/Dubai" => "(GMT+04:00) Abu Dhabi, Muscat",
            "Asia/Yerevan" => "(GMT+04:00) Yerevan",
            "Asia/Kabul" => "(GMT+04:30) Kabul",
            "Asia/Yekaterinburg" => "(GMT+05:00) Ekaterinburg",
            "Asia/Tashkent" => "(GMT+05:00) Tashkent",
            "Asia/Kolkata" => "(GMT+05:30) Chennai, Kolkata, Mumbai, New Delhi",
            "Asia/Katmandu" => "(GMT+05:45) Kathmandu",
            "Asia/Dhaka" => "(GMT+06:00) Astana, Dhaka",
            "Asia/Novosibirsk" => "(GMT+06:00) Novosibirsk",
            "Asia/Rangoon" => "(GMT+06:30) Yangon (Rangoon)",
            "Asia/Bangkok" => "(GMT+07:00) Bangkok, Hanoi, Jakarta",
            "Asia/Krasnoyarsk" => "(GMT+07:00) Krasnoyarsk",
            "Asia/Hong_Kong" => "(GMT+08:00) Beijing, Chongqing, Hong Kong, Urumqi",
            "Asia/Irkutsk" => "(GMT+08:00) Irkutsk, Ulaan Bataar",
            "Australia/Perth" => "(GMT+08:00) Perth",
            "Australia/Eucla" => "(GMT+08:45) Eucla",
            "Asia/Tokyo" => "(GMT+09:00) Osaka, Sapporo, Tokyo",
            "Asia/Seoul" => "(GMT+09:00) Seoul",
            "Asia/Yakutsk" => "(GMT+09:00) Yakutsk",
            "Australia/Adelaide" => "(GMT+09:30) Adelaide",
            "Australia/Darwin" => "(GMT+09:30) Darwin",
            "Australia/Brisbane" => "(GMT+10:00) Brisbane",
            "Australia/Hobart" => "(GMT+10:00) Hobart",
            "Asia/Vladivosto" => "(GMT+10:00) Vladivostok",
            "Australia/Lord_Howe" => "(GMT+10:30) Lord Howe Island",
            "Etc/GMT-11" => "(GMT+11:00) Solomon Is., New Caledonia",
            "Asia/Magadan" => "(GMT+11:00) Magadan",
            "Pacific/Norfolk" => "(GMT+11:30) Norfolk Island",
            "Asia/Anadyr" => "(GMT+12:00) Anadyr, Kamchatka",
            "Pacific/Auckland" => "(GMT+12:00) Auckland, Wellington",
            "Etc/GMT-12" => "(GMT+12:00) Fiji, Kamchatka, Marshall Is.",
            "Pacific/Chatham" => "(GMT+12:45) Chatham Islands",
            "Pacific/Tongatapu" => "(GMT+13:00) Nuku'alofa",
            "Pacific/Kiritimati" => "(GMT+14:00) Kiritimati",
        ];
    }
}