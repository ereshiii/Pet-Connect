<?php

namespace App\Services;

use Carbon\Carbon;

class ClinicOperatingStatusService
{
    /**
     * Get the current operating status of a clinic
     */
    public static function getOperatingStatus($operatingHours, $timezone = 'Asia/Manila')
    {
        if (!$operatingHours || !is_array($operatingHours)) {
            return [
                'is_open' => false,
                'status' => 'Closed',
                'status_color' => 'text-red-600 dark:text-red-400',
                'message' => 'Hours not available',
                'next_change' => null,
                'is_24_7' => false
            ];
        }

        $now = Carbon::now($timezone);
        $currentDay = strtolower($now->format('l')); // monday, tuesday, etc.
        $currentTime = $now->format('H:i');

        // Check if clinic operates 24/7
        if (self::isClinic24_7($operatingHours)) {
            return [
                'is_open' => true,
                'status' => 'Open 24/7',
                'status_color' => 'text-green-600 dark:text-green-400',
                'message' => 'Open 24 hours',
                'next_change' => null,
                'is_24_7' => true
            ];
        }

        // Get today's operating hours
        $todayHours = self::getTodayOperatingHours($operatingHours, $currentDay);
        
        if (!$todayHours) {
            return [
                'is_open' => false,
                'status' => 'Closed',
                'status_color' => 'text-red-600 dark:text-red-400',
                'message' => 'Closed today',
                'next_change' => self::getNextOpeningTime($operatingHours, $now),
                'is_24_7' => false
            ];
        }

        // Get standardized opening and closing times
        $openTime = self::getOpenTime($todayHours);
        $closeTime = self::getCloseTime($todayHours);

        if (!$openTime || !$closeTime) {
            return [
                'is_open' => false,
                'status' => 'Closed',
                'status_color' => 'text-red-600 dark:text-red-400',
                'message' => 'Hours not available',
                'next_change' => null,
                'is_24_7' => false
            ];
        }

        // Check if currently open
        $isOpen = self::isCurrentlyOpen($openTime, $closeTime, $currentTime);
        
        if ($isOpen) {
            return [
                'is_open' => true,
                'status' => 'Open Now',
                'status_color' => 'text-green-600 dark:text-green-400',
                'message' => 'Open until ' . self::formatTime($closeTime),
                'next_change' => [
                    'action' => 'closes',
                    'time' => $closeTime,
                    'formatted' => self::formatTime($closeTime)
                ],
                'is_24_7' => false,
            ];
        } else {
            // Clinic is closed, check if it opens later today
            $opensLaterToday = $currentTime < $openTime;
            
            if ($opensLaterToday) {
                return [
                    'is_open' => false,
                    'status' => 'Closed',
                    'status_color' => 'text-red-600 dark:text-red-400',
                    'message' => 'Opens at ' . self::formatTime($openTime),
                    'next_change' => [
                        'action' => 'opens',
                        'time' => $openTime,
                        'formatted' => self::formatTime($openTime)
                    ],
                    'is_24_7' => false,
                ];
            } else {
                // Closed for the day, find next opening
                return [
                    'is_open' => false,
                    'status' => 'Closed',
                    'status_color' => 'text-red-600 dark:text-red-400',
                    'message' => 'Closed for today',
                    'next_change' => self::getNextOpeningTime($operatingHours, $now),
                    'is_24_7' => false,
                ];
            }
        }
    }

    /**
     * Get standardized opening time from hours array
     */
    private static function getOpenTime($hours)
    {
        return $hours['open'] ?? $hours['opening_time'] ?? null;
    }

    /**
     * Get standardized closing time from hours array
     */
    private static function getCloseTime($hours)
    {
        return $hours['close'] ?? $hours['closing_time'] ?? null;
    }

    /**
     * Check if clinic operates 24/7
     */
    public static function isClinic24_7($operatingHours)
    {
        if (!is_array($operatingHours)) {
            return false;
        }

        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        
        foreach ($days as $day) {
            $hours = $operatingHours[$day] ?? null;
            if (!$hours || 
                isset($hours['closed']) || 
                (self::getOpenTime($hours) !== '00:00' && self::getCloseTime($hours) !== '23:59') &&
                (self::getOpenTime($hours) !== '00:00:00' && self::getCloseTime($hours) !== '23:59:59')) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Get today's operating hours
     */
    private static function getTodayOperatingHours($operatingHours, $day)
    {
        $todayHours = $operatingHours[$day] ?? null;
        
        if (!$todayHours || isset($todayHours['closed'])) {
            return null;
        }
        
        return $todayHours;
    }

    /**
     * Check if clinic is currently open based on time
     */
    private static function isCurrentlyOpen($openTime, $closeTime, $currentTime)
    {
        if (!$openTime || !$closeTime) {
            return false;
        }

        return $currentTime >= $openTime && $currentTime <= $closeTime;
    }

    /**
     * Get the next opening time
     */
    private static function getNextOpeningTime($operatingHours, $currentDateTime)
    {
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $currentDayIndex = $currentDateTime->dayOfWeek === 0 ? 6 : $currentDateTime->dayOfWeek - 1; // Convert to Monday = 0
        
        // Check next 7 days
        for ($i = 1; $i <= 7; $i++) {
            $checkDayIndex = ($currentDayIndex + $i) % 7;
            $checkDay = $days[$checkDayIndex];
            $hours = $operatingHours[$checkDay] ?? null;
            
            if ($hours && !isset($hours['closed']) && self::getOpenTime($hours)) {
                $nextDate = $currentDateTime->copy()->addDays($i);
                
                return [
                    'action' => 'opens',
                    'day' => ucfirst($checkDay),
                    'date' => $nextDate->format('Y-m-d'),
                    'time' => self::getOpenTime($hours),
                    'formatted' => self::formatTime(self::getOpenTime($hours)),
                    'full_message' => 'Opens ' . ucfirst($checkDay) . ' at ' . self::formatTime(self::getOpenTime($hours))
                ];
            }
        }
        
        return null;
    }

    /**
     * Format time for display
     */
    public static function formatTime($time)
    {
        if (!$time) {
            return 'N/A';
        }
        
        // Handle both H:i and H:i:s formats
        $timeParts = explode(':', $time);
        $hour = (int) $timeParts[0];
        $minute = (int) $timeParts[1];
        
        $period = $hour >= 12 ? 'PM' : 'AM';
        $displayHour = $hour === 0 ? 12 : ($hour > 12 ? $hour - 12 : $hour);
        
        return sprintf('%d:%02d %s', $displayHour, $minute, $period);
    }

    /**
     * Get weekly schedule for display
     */
    public static function getWeeklySchedule($operatingHours)
    {
        if (!$operatingHours || !is_array($operatingHours)) {
            return [];
        }

        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $schedule = [];
        
        foreach ($days as $day) {
            $hours = $operatingHours[$day] ?? null;
            
            if (!$hours || isset($hours['closed'])) {
                $schedule[$day] = [
                    'day' => ucfirst($day),
                    'is_closed' => true,
                    'opening_time' => null,
                    'closing_time' => null,
                    'formatted' => 'Closed'
                ];
            } else {
                $openTime = self::getOpenTime($hours);
                $closeTime = self::getCloseTime($hours);
                
                $schedule[$day] = [
                    'day' => ucfirst($day),
                    'is_closed' => false,
                    'opening_time' => $openTime,
                    'closing_time' => $closeTime,
                    'formatted' => self::formatTime($openTime) . ' - ' . self::formatTime($closeTime)
                ];
            }
        }
        
        return $schedule;
    }

    /**
     * Check if clinic has emergency hours
     */
    public static function hasEmergencyHours($operatingHours)
    {
        if (!$operatingHours || !is_array($operatingHours)) {
            return false;
        }

        // Check if any day has emergency hours flag or 24/7 operation
        foreach ($operatingHours as $day => $hours) {
            if (isset($hours['emergency']) && $hours['emergency']) {
                return true;
            }
        }

        return self::isClinic24_7($operatingHours);
    }
}