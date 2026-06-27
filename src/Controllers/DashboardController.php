<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Models\Activity;
use App\Models\Project;

class DashboardController {
    public function index() {
        Auth::guard();

        $user = Auth::user();
        $today = date('Y-m-d');

        // Always calculate today separately for the stat box
        $todayActivities = Activity::where('user_id', $user->id, [
            'date_from' => $today,
            'date_to' => $today
        ]);
        $todayTotal = array_sum(array_column($todayActivities, 'duration_minutes'));

        // Choose which period to display in the activity list
        $activities = $todayActivities;
        $activityPeriod = 'Dzisiaj';

        if (empty($activities)) {
            $thirtyDaysAgo = date('Y-m-d', strtotime('-30 days'));
            $activities = Activity::where('user_id', $user->id, [
                'date_from' => $thirtyDaysAgo,
                'date_to' => $today
            ]);
            $activityPeriod = 'Ostatnie 30 dni';
        }

        if (empty($activities)) {
            $twoMonthsAgo = date('Y-m-d', strtotime('-2 months'));
            $activities = Activity::where('user_id', $user->id, [
                'date_from' => $twoMonthsAgo,
                'date_to' => $today
            ]);
            $activityPeriod = 'Ostatnie 2 miesiące';
        }

        // Last 7 days total
        $sevenDaysAgo = date('Y-m-d', strtotime('-6 days'));
        $allUserActivities = Activity::where('user_id', $user->id);
        $weekActivities = array_filter($allUserActivities, fn($a) => $a->date >= $sevenDaysAgo && $a->date <= $today);
        $weekTotal = array_sum(array_map(fn($a) => $a->duration_minutes, $weekActivities));

        // Calculate unbilled summary
        $allActivities = Activity::where('user_id', $user->id);
        $totalMinutes = array_sum(array_column($allActivities, 'duration_minutes'));
        $unbilledMinutes = array_sum(
            array_map(fn($a) => !$a->is_billed ? $a->duration_minutes : 0, $allActivities)
        );

        // Group by project for finances
        $byProject = [];
        $projectFinances = [];
        foreach ($allActivities as $activity) {
            $pid = $activity->project_id;
            if (!isset($byProject[$pid])) {
                $byProject[$pid] = [];
                $projectFinances[$pid] = ['unbilled_minutes' => 0, 'rate' => 0];
            }
            $byProject[$pid][] = $activity;

            if (!$activity->is_billed) {
                $projectFinances[$pid]['unbilled_minutes'] += $activity->duration_minutes;
            }
        }

        // Set hourly rates
        foreach ($projectFinances as $pid => &$finance) {
            $project = Project::find($pid);
            $finance['rate'] = $project->hourly_rate ?? 0;
        }

        echo view('dashboard.index', [
            'user' => $user,
            'activities' => $activities,
            'activityPeriod' => $activityPeriod,
            'weekTotal' => minutes_to_time($weekTotal),
            'todayTotal' => $todayTotal,
            'totalMinutes' => $totalMinutes,
            'unbilledMinutes' => $unbilledMinutes,
            'byProject' => $byProject,
            'projectFinances' => $projectFinances,
            '_debug' => ['weekStart' => $weekStart, 'weekEnd' => $weekEnd, 'dates' => $debugDates]
        ]);
    }
}
