<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Models\Activity;
use App\Models\Client;
use App\Models\Project;
use Mpdf\Mpdf;

class ReportController {
    public function index() {
        Auth::guard();

        $user = Auth::user();
        $dateFrom = $_GET['date_from'] ?? date('Y-m-d', strtotime('first day of this month'));
        $dateTo = $_GET['date_to'] ?? date('Y-m-d');
        $status = $_GET['status'] ?? 'all';
        $client_id = $_GET['client_id'] ?? null;
        $project_id = $_GET['project_id'] ?? null;

        // Build query
        $filters = [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'status' => $status === 'all' ? null : $status
        ];

        $activities = [];
        if ($user->isAdmin()) {
            $sql = 'SELECT a.* FROM activities a WHERE a.date >= ? AND a.date <= ?';
            $params = [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'];

            if ($status !== 'all') {
                $sql .= ' AND a.is_billed = ?';
                $params[] = $status === 'billed' ? 1 : 0;
            }

            if ($project_id) {
                $sql .= ' AND a.project_id = ?';
                $params[] = $project_id;
            }

            $sql .= ' ORDER BY a.date DESC, a.time_from DESC';
            $rows = Database::all($sql, $params);
            $activities = array_map(fn($r) => Activity::hydrate($r), $rows);
        } else {
            $activities = Activity::where('user_id', $user->id, $filters);
            if ($project_id) {
                $activities = array_filter($activities, fn($a) => $a->project_id == $project_id);
            }
        }

        // Calculate totals
        $total_minutes = array_sum(array_column($activities, 'duration_minutes'));
        $billed_minutes = array_sum(
            array_map(fn($a) => $a->is_billed ? $a->duration_minutes : 0, $activities)
        );

        $clients = Client::all(true);
        $projects = Project::all(true);

        // Group by project and calculate finances
        $byProject = [];
        $projectFinances = [];
        foreach ($activities as $activity) {
            $pid = $activity->project_id;
            if (!isset($byProject[$pid])) {
                $byProject[$pid] = [];
                $projectFinances[$pid] = ['unbilled_minutes' => 0, 'rate' => 0];
            }
            $byProject[$pid][] = $activity;

            // Calculate unbilled hours for finance
            if (!$activity->is_billed) {
                $projectFinances[$pid]['unbilled_minutes'] += $activity->duration_minutes;
            }
        }

        // Set hourly rates
        foreach ($projectFinances as $pid => &$finance) {
            $project = Project::find($pid);
            $finance['rate'] = $project->hourly_rate ?? 0;
        }

        echo view('reports.index', [
            'user' => $user,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'status' => $status,
            'project_id' => $project_id,
            'client_id' => $client_id,
            'activities' => $activities,
            'byProject' => $byProject,
            'projectFinances' => $projectFinances,
            'totalMinutes' => $total_minutes,
            'billedMinutes' => $billed_minutes,
            'clients' => $clients,
            'projects' => $projects
        ]);
    }

    public function export() {
        Auth::guard();

        $user = Auth::user();
        $dateFrom = $_GET['date_from'] ?? date('Y-m-d', strtotime('first day of this month'));
        $dateTo = $_GET['date_to'] ?? date('Y-m-d');
        $status = $_GET['status'] ?? 'all';
        $project_id = $_GET['project_id'] ?? null;

        // Get activities (same as report)
        $sql = 'SELECT a.* FROM activities a WHERE a.date >= ? AND a.date <= ?';
        $params = [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'];

        if (!$user->isAdmin()) {
            $sql .= ' AND a.user_id = ?';
            $params[] = $user->id;
        }

        if ($status !== 'all') {
            $sql .= ' AND a.is_billed = ?';
            $params[] = $status === 'billed' ? 1 : 0;
        }

        if ($project_id) {
            $sql .= ' AND a.project_id = ?';
            $params[] = $project_id;
        }

        $sql .= ' ORDER BY a.date DESC, a.time_from DESC';
        $rows = Database::all($sql, $params);
        $activities = array_map(fn($r) => Activity::hydrate($r), $rows);

        $total_minutes = array_sum(array_column($activities, 'duration_minutes'));

        // Generate HTML for PDF
        $html = $this->generatePDF($activities, $dateFrom, $dateTo, $total_minutes);

        // Create PDF
        try {
            $mpdf = new Mpdf();
            $mpdf->WriteHTML($html);
            $fromSlug = date('Ymd', strtotime($dateFrom));
            $toSlug   = date('Ymd', strtotime($dateTo));
            $filename = 'PGMS-Tracker_SummaryReport_' . $fromSlug . '-' . $toSlug . '.pdf';
            $mpdf->Output($filename, 'D');
        } catch (\Exception $e) {
            die('PDF Error: ' . $e->getMessage());
        }
    }

    private function generatePDF($activities, $dateFrom, $dateTo, $totalMinutes) {
        $byProject = [];
        $byDescription = [];
        $byDate = [];

        foreach ($activities as $activity) {
            $pid = $activity->project_id;
            $project = Project::find($pid);
            $color = $project->color ?? '#f59e0b';

            if (!isset($byProject[$pid])) {
                $byProject[$pid] = ['minutes' => 0, 'name' => $project->name, 'color' => $color, 'rate' => (float)($project->hourly_rate ?? 0), 'descriptions' => []];
            }
            $byProject[$pid]['minutes'] += $activity->duration_minutes;

            $desc = $activity->description;
            if (!isset($byProject[$pid]['descriptions'][$desc])) {
                $byProject[$pid]['descriptions'][$desc] = 0;
            }
            $byProject[$pid]['descriptions'][$desc] += $activity->duration_minutes;

            if (!isset($byDescription[$desc])) {
                $byDescription[$desc] = ['minutes' => 0, 'color' => $color];
            }
            $byDescription[$desc]['minutes'] += $activity->duration_minutes;

            $date = $activity->date;
            if (!isset($byDate[$date])) {
                $byDate[$date] = ['minutes' => 0, 'color' => $color];
            }
            $byDate[$date]['minutes'] += $activity->duration_minutes;
        }

        uasort($byProject, fn($a, $b) => $b['minutes'] - $a['minutes']);
        uasort($byDescription, fn($a, $b) => $b['minutes'] - $a['minutes']);

        $totalFormatted = minutes_to_time($totalMinutes);
        $dateFromFmt = date('m/d/Y', strtotime($dateFrom));
        $dateToFmt   = date('m/d/Y', strtotime($dateTo));
        $primaryColor = reset($byProject)['color'] ?? '#f59e0b';

        $barSvg     = $this->buildBarChart($byDate, $dateFrom, $dateTo, $primaryColor);
        $projectSvg = $this->buildDonut(array_map(fn($p) => ['minutes' => $p['minutes'], 'color' => $p['color']], $byProject), $totalMinutes, $totalFormatted);
        $descSvg    = $this->buildDonut(array_map(fn($d) => ['minutes' => $d['minutes'], 'color' => $d['color']], $byDescription), $totalMinutes, $totalFormatted);

        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>
            * { margin:0; padding:0; box-sizing:border-box; }
            body { font-family: Arial, Helvetica, sans-serif; font-size:11px; color:#333; }
            h1 { font-size:22px; font-weight:normal; margin-bottom:3px; }
            .sub { color:#666; font-size:11px; margin-bottom:6px; }
            .total { font-size:28px; font-weight:bold; margin-bottom:14px; }
            .section-title { font-size:15px; font-weight:bold; border-bottom:1px solid #ddd; padding-bottom:5px; margin:18px 0 12px; }
            .dot { display:inline-block; width:8px; height:8px; border-radius:4px; margin-right:5px; }
            td { vertical-align:top; }
        </style></head><body>';

        $logoSvg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 180 180" width="48" height="48"><defs><linearGradient id="lg" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" style="stop-color:#667eea"/><stop offset="100%" style="stop-color:#764ba2"/></linearGradient></defs><circle cx="90" cy="90" r="90" fill="url(#lg)"/><circle cx="90" cy="90" r="65" fill="none" stroke="white" stroke-width="4"/><line x1="90" y1="30" x2="90" y2="40" stroke="white" stroke-width="3"/><line x1="150" y1="90" x2="140" y2="90" stroke="white" stroke-width="3"/><line x1="90" y1="150" x2="90" y2="140" stroke="white" stroke-width="3"/><line x1="30" y1="90" x2="40" y2="90" stroke="white" stroke-width="3"/><line x1="90" y1="90" x2="90" y2="55" stroke="white" stroke-width="5" stroke-linecap="round"/><line x1="90" y1="90" x2="90" y2="35" stroke="white" stroke-width="3" stroke-linecap="round" opacity="0.8"/><circle cx="90" cy="90" r="6" fill="white"/></svg>';

        // --- Page 1 ---
        $html .= '<table width="100%" cellpadding="0" cellspacing="0"><tr>';
        $html .= '<td valign="bottom"><h1>Summary report</h1><p class="sub">' . $dateFromFmt . ' - ' . $dateToFmt . '</p></td>';
        $html .= '<td width="140" align="center" valign="middle" style="padding-bottom:4px;text-align:center;">' . $logoSvg . '<br><span style="font-size:10px;color:#667eea;font-weight:bold;">PGMS Time Tracker</span></td>';
        $html .= '</tr></table>';
        $html .= '<p class="total">Total: ' . $totalFormatted . '</p>';
        $html .= $barSvg;

        // Projects
        $html .= '<p class="section-title">Project</p>';
        $html .= '<table width="100%" cellpadding="0" cellspacing="0"><tr>';
        $html .= '<td width="130">' . $projectSvg . '</td>';
        $html .= '<td style="padding-left:20px;vertical-align:middle;">';
        $html .= '<table width="100%" cellpadding="4" cellspacing="0">';
        foreach ($byProject as $data) {
            $pct   = $totalMinutes ? number_format(($data['minutes'] / $totalMinutes) * 100, 2) : '0.00';
            $hours = $data['minutes'] / 60;
            $cost  = $data['rate'] > 0 ? number_format($hours * $data['rate'], 2, ',', ' ') . ' zł' : '—';
            $html .= '<tr style="border-bottom:1px solid #f0f0f0;">';
            $html .= '<td><span class="dot" style="background:' . htmlspecialchars($data['color']) . '"></span>' . htmlspecialchars($data['name']) . '</td>';
            $html .= '<td width="60" align="right">' . minutes_to_time($data['minutes']) . '</td>';
            $html .= '<td width="50" align="right" style="color:#999;">' . $pct . '%</td>';
            $html .= '<td width="80" align="right" style="color:#b45309;font-weight:bold;">' . $cost . '</td>';
            $html .= '</tr>';
        }
        $html .= '</table></td></tr></table>';

        // Descriptions
        $html .= '<p class="section-title">Description</p>';
        $html .= '<table width="100%" cellpadding="0" cellspacing="0"><tr>';
        $html .= '<td width="130">' . $descSvg . '</td>';
        $html .= '<td style="padding-left:20px;vertical-align:middle;">';
        $html .= '<table width="100%" cellpadding="4" cellspacing="0">';
        foreach ($byDescription as $desc => $data) {
            $pct = $totalMinutes ? number_format(($data['minutes'] / $totalMinutes) * 100, 2) : '0.00';
            $html .= '<tr style="border-bottom:1px solid #f0f0f0;">';
            $html .= '<td><span class="dot" style="background:' . htmlspecialchars($data['color']) . '"></span>' . htmlspecialchars($desc) . '</td>';
            $html .= '<td width="70" align="right">' . minutes_to_time($data['minutes']) . '</td>';
            $html .= '<td width="55" align="right" style="color:#999;">' . $pct . '%</td>';
            $html .= '</tr>';
        }
        $html .= '</table></td></tr></table>';

        // --- Page 2 ---
        $html .= '<pagebreak />';
        $html .= '<table width="100%" cellpadding="7" cellspacing="0" style="border-collapse:collapse;">';
        $html .= '<tr><td style="font-weight:bold;border-bottom:2px solid #ddd;font-size:12px;">Project / Description</td>';
        $html .= '<td width="90" align="right" style="font-weight:bold;border-bottom:2px solid #ddd;font-size:12px;">Duration</td></tr>';
        foreach ($byProject as $data) {
            $html .= '<tr style="background:#fafafa;">';
            $html .= '<td style="font-weight:bold;border-bottom:1px solid #e0e0e0;padding-top:10px;">';
            $html .= '<span class="dot" style="background:' . htmlspecialchars($data['color']) . '"></span>' . htmlspecialchars($data['name']) . '</td>';
            $html .= '<td align="right" style="font-weight:bold;border-bottom:1px solid #e0e0e0;padding-top:10px;">' . minutes_to_time($data['minutes']) . '</td>';
            $html .= '</tr>';
            arsort($data['descriptions']);
            foreach ($data['descriptions'] as $desc => $mins) {
                $html .= '<tr>';
                $html .= '<td style="padding-left:20px;color:#555;border-bottom:1px solid #f0f0f0;">' . htmlspecialchars($desc) . '</td>';
                $html .= '<td align="right" style="color:#555;border-bottom:1px solid #f0f0f0;">' . minutes_to_time($mins) . '</td>';
                $html .= '</tr>';
            }
        }
        $html .= '</table>';
        $html .= '</body></html>';
        return $html;
    }

    private function buildBarChart(array $byDate, string $dateFrom, string $dateTo, string $color): string {
        $start = strtotime($dateFrom);
        $end   = strtotime($dateTo);
        $days  = [];
        for ($ts = $start; $ts <= $end; $ts += 86400) {
            $d = date('Y-m-d', $ts);
            $days[$d] = isset($byDate[$d]) ? $byDate[$d]['minutes'] : 0;
        }

        $numDays    = count($days);
        $maxMinutes = max(array_values($days) ?: [60]);
        $maxHours   = max(0.5, ceil($maxMinutes / 60 / 0.5) * 0.5);

        $W = 520; $H = 160;
        $pL = 32; $pB = 28; $pT = 8; $pR = 8;
        $cW = $W - $pL - $pR;
        $cH = $H - $pB - $pT;
        $barW = max(2, floor($cW / $numDays) - 1);

        $svg = '<svg width="' . $W . '" height="' . $H . '" xmlns="http://www.w3.org/2000/svg" style="margin-bottom:8px;">';

        // Gridlines & Y labels
        $steps = 5;
        for ($i = 0; $i <= $steps; $i++) {
            $h = ($i / $steps) * $maxHours;
            $y = $pT + $cH - ($i / $steps) * $cH;
            $svg .= '<line x1="' . $pL . '" y1="' . round($y) . '" x2="' . ($W - $pR) . '" y2="' . round($y) . '" stroke="#ebebeb" stroke-width="1"/>';
            $svg .= '<text x="' . ($pL - 3) . '" y="' . (round($y) + 3) . '" text-anchor="end" font-size="8" fill="#bbb">' . number_format($h, 1) . 'h</text>';
        }

        // Bars & X labels
        $i = 0;
        $labelEvery = max(1, intdiv($numDays, 8));
        foreach ($days as $date => $minutes) {
            $x  = $pL + ($i / $numDays) * $cW;
            $bH = $minutes > 0 ? ($minutes / ($maxHours * 60)) * $cH : 0;
            $y  = $pT + $cH - $bH;
            if ($minutes > 0) {
                $svg .= '<rect x="' . round($x, 1) . '" y="' . round($y, 1) . '" width="' . $barW . '" height="' . round($bH, 1) . '" fill="' . htmlspecialchars($color) . '" rx="1"/>';
            }
            if ($i % $labelEvery === 0) {
                $parts = explode('-', $date);
                $label = ltrim($parts[2], '0') . '.' . ltrim($parts[1], '0');
                $lx = $x + $barW / 2;
                $svg .= '<text x="' . round($lx, 1) . '" y="' . ($H - 4) . '" text-anchor="middle" font-size="7" fill="#bbb">' . $label . '</text>';
            }
            $i++;
        }

        $svg .= '</svg>';
        return $svg;
    }

    private function buildDonut(array $items, int $totalMinutes, string $centerText): string {
        if ($totalMinutes === 0 || empty($items)) return '';

        $cx = 60; $cy = 60; $R = 52; $ri = 36;

        $svg  = '<svg width="120" height="120" xmlns="http://www.w3.org/2000/svg">';
        $svg .= '<circle cx="' . $cx . '" cy="' . $cy . '" r="' . $R . '" fill="#eeeeee"/>';
        $svg .= '<circle cx="' . $cx . '" cy="' . $cy . '" r="' . $ri . '" fill="white"/>';

        $startAngle = -M_PI / 2; // start at 12 o'clock

        foreach ($items as $item) {
            $portion  = $item['minutes'] / $totalMinutes;
            $endAngle = $startAngle + $portion * 2 * M_PI;
            $large    = ($endAngle - $startAngle > M_PI) ? 1 : 0;

            $ox1 = round($cx + $R  * cos($startAngle), 3);
            $oy1 = round($cy + $R  * sin($startAngle), 3);
            $ox2 = round($cx + $R  * cos($endAngle),   3);
            $oy2 = round($cy + $R  * sin($endAngle),   3);
            $ix1 = round($cx + $ri * cos($startAngle), 3);
            $iy1 = round($cy + $ri * sin($startAngle), 3);
            $ix2 = round($cx + $ri * cos($endAngle),   3);
            $iy2 = round($cy + $ri * sin($endAngle),   3);

            $d = "M $ox1 $oy1 A $R $R 0 $large 1 $ox2 $oy2 L $ix2 $iy2 A $ri $ri 0 $large 0 $ix1 $iy1 Z";
            $svg .= '<path d="' . $d . '" fill="' . htmlspecialchars($item['color']) . '"/>';

            $startAngle = $endAngle;
        }

        $svg .= '<text x="' . $cx . '" y="' . ($cy + 4) . '" text-anchor="middle" font-size="10" font-weight="bold" fill="#333">' . htmlspecialchars($centerText) . '</text>';
        $svg .= '</svg>';
        return $svg;
    }
}
