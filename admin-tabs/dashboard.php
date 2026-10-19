<?php
/**
 * Dashboard Tab - EXACT replica of React AdminDashboard dashboard case
 */
// Get about content for statistics
$aboutContent = $db->fetchOne("SELECT * FROM about_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?? [];
$aboutStats = [];
if (!empty($aboutContent['statistics'])) {
    $aboutStats = json_decode($aboutContent['statistics'], true) ?: [];
}

// Calculate statistics from about content
$totalStudents = '0';
$successRate = '0%';
$totalCourses = '0';
$totalAwards = '0';

foreach ($aboutStats as $stat) {
    $label = strtolower($stat['label'] ?? '');
    if (strpos($label, 'student') !== false) {
        $totalStudents = $stat['value'] ?? '0';
    } elseif (strpos($label, 'success') !== false) {
        $successRate = $stat['value'] ?? '0%';
    } elseif (strpos($label, 'course') !== false) {
        $totalCourses = $stat['value'] ?? '0';
    } elseif (strpos($label, 'award') !== false) {
        $totalAwards = $stat['value'] ?? '0';
    }
}

// Get activities for upcoming events calculation
$activities = $db->fetchAll("SELECT * FROM activities WHERE is_active = 1 ORDER BY date DESC");
$currentMonth = (int)date('n') - 1; // JavaScript month is 0-indexed
$currentYear = (int)date('Y');
$upcomingEvents = 0;

foreach ($activities as $activity) {
    if (!empty($activity['date'])) {
        $activityDate = new DateTime($activity['date']);
        if ($activityDate->format('n') - 1 == $currentMonth && (int)$activityDate->format('Y') == $currentYear) {
            $upcomingEvents++;
        }
    }
}

// Get recent activities (last 5, sorted by date)
$recentActivities = [];
foreach ($activities as $activity) {
    $recentActivities[] = [
        'title' => $activity['title'] ?? 'Untitled Activity',
        'date' => !empty($activity['date']) ? date('M d, Y', strtotime($activity['date'])) : 'No date',
        'timeAgo' => !empty($activity['date']) ? getTimeAgo(new DateTime($activity['date'])) : 'Unknown'
    ];
}
usort($recentActivities, function($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});
$recentActivities = array_slice($recentActivities, 0, 5);

// Helper function to calculate time ago
function getTimeAgo($date) {
    $now = new DateTime();
    $diff = $now->diff($date);
    
    if ($diff->s < 60) return 'Just now';
    if ($diff->i < 60) return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
    if ($diff->h < 24) return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    if ($diff->d < 7) return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
    $weeks = floor($diff->d / 7);
    return $weeks . ' week' . ($weeks > 1 ? 's' : '') . ' ago';
}

// Calculate school statistics
$teachersCount = $stats['teachers'];
$facilitiesCount = $stats['facilities'];
$testimonialsCount = $stats['testimonials'];
$newsCount = $stats['news'];

// Student distribution data (mock for now)
$studentDistributionData = [
    ['name' => 'Enrolled', 'value' => 64, 'color' => '#10B981'],
    ['name' => 'Pending', 'value' => 36, 'color' => '#8B5CF6'],
    ['name' => 'Waitlist', 'value' => 0, 'color' => '#F59E0B']
];

// Monthly enrollment data
$months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
$monthlyEnrollmentData = [];
foreach ($months as $index => $month) {
    $monthActivities = 0;
    foreach ($activities as $activity) {
        if (!empty($activity['date'])) {
            $activityDate = new DateTime($activity['date']);
            if ($activityDate->format('n') - 1 == $index && (int)$activityDate->format('Y') == $currentYear) {
                $monthActivities++;
            }
        }
    }
    $baseEnrollment = $index == 7 ? 1100 : rand(50, 150);
    $monthlyEnrollmentData[] = ['month' => $month, 'amount' => $baseEnrollment + ($monthActivities * 10)];
}
?>
<div class="content-card" style="padding: 0;">
    <div style="padding: 30px;">
        <div style="margin-bottom: 30px;">
            <h1 style="font-size: 30px; font-weight: bold; color: #111827; margin: 0;">School Management Dashboard</h1>
            <p style="color: #6b7280; margin-top: 8px;">Welcome back, <?php echo htmlspecialchars($currentUser['username'] ?? 'Admin'); ?>!</p>
        </div>

        <!-- KPI Cards - Top Section -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 24px; margin-bottom: 30px;">
            <!-- Student Enrollment Card -->
            <div style="background: linear-gradient(135deg, #3b82f6, #2563eb); border-radius: 12px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); padding: 24px; color: white; transform: scale(1); transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <p style="font-size: 14px; font-weight: 500; opacity: 0.9; margin: 0 0 8px 0;">Total Students</p>
                        <p style="font-size: 24px; font-weight: bold; margin: 0 0 4px 0;"><?php echo htmlspecialchars($totalStudents); ?></p>
                        <p style="font-size: 12px; opacity: 0.8; margin: 0;">Active enrollment</p>
                    </div>
                    <div style="width: 48px; height: 48px; background: rgba(255,255,255,0.2); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <span style="font-size: 24px;">👥</span>
                    </div>
                </div>
            </div>

            <!-- Academic Performance Card -->
            <div style="background: linear-gradient(135deg, #10b981, #059669); border-radius: 12px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); padding: 24px; color: white; transform: scale(1); transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <p style="font-size: 14px; font-weight: 500; opacity: 0.9; margin: 0 0 8px 0;">Success Rate</p>
                        <p style="font-size: 24px; font-weight: bold; margin: 0 0 4px 0;"><?php echo htmlspecialchars($successRate); ?></p>
                        <p style="font-size: 12px; opacity: 0.8; margin: 0;">Academic excellence</p>
                    </div>
                    <div style="width: 48px; height: 48px; background: rgba(255,255,255,0.2); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <span style="font-size: 24px;">🏆</span>
                    </div>
                </div>
            </div>

            <!-- Courses Card -->
            <div style="background: linear-gradient(135deg, #8b5cf6, #7c3aed); border-radius: 12px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); padding: 24px; color: white; transform: scale(1); transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <p style="font-size: 14px; font-weight: 500; opacity: 0.9; margin: 0 0 8px 0;">Active Courses</p>
                        <p style="font-size: 24px; font-weight: bold; margin: 0 0 4px 0;"><?php echo htmlspecialchars($totalCourses); ?></p>
                        <p style="font-size: 12px; opacity: 0.8; margin: 0;">Curriculum programs</p>
                    </div>
                    <div style="width: 48px; height: 48px; background: rgba(255,255,255,0.2); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <span style="font-size: 24px;">📚</span>
                    </div>
                </div>
            </div>

            <!-- Activities Card -->
            <div style="background: linear-gradient(135deg, #f97316, #ea580c); border-radius: 12px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); padding: 24px; color: white; transform: scale(1); transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <p style="font-size: 14px; font-weight: 500; opacity: 0.9; margin: 0 0 8px 0;">Upcoming Events</p>
                        <p style="font-size: 24px; font-weight: bold; margin: 0 0 4px 0;"><?php echo $upcomingEvents; ?></p>
                        <p style="font-size: 12px; opacity: 0.8; margin: 0;">This month</p>
                    </div>
                    <div style="width: 48px; height: 48px; background: rgba(255,255,255,0.2); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <span style="font-size: 24px;">📅</span>
                    </div>
                </div>
            </div>

            <!-- Awards Card -->
            <div style="background: linear-gradient(135deg, #ec4899, #db2777); border-radius: 12px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); padding: 24px; color: white; transform: scale(1); transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <p style="font-size: 14px; font-weight: 500; opacity: 0.9; margin: 0 0 8px 0;">Total Awards</p>
                        <p style="font-size: 24px; font-weight: bold; margin: 0 0 4px 0;"><?php echo htmlspecialchars($totalAwards); ?></p>
                        <p style="font-size: 12px; opacity: 0.8; margin: 0;">Achievements</p>
                    </div>
                    <div style="width: 48px; height: 48px; background: rgba(255,255,255,0.2); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <span style="font-size: 24px;">⭐</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section - Middle -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 24px; margin-bottom: 30px;">
            <!-- Student Distribution Chart -->
            <div style="background: white; border-radius: 12px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); padding: 24px;">
                <h3 style="font-size: 18px; font-weight: 500; color: #111827; margin: 0 0 16px 0;">Student Distribution</h3>
                <div style="height: 256px; position: relative;">
                    <canvas id="studentDistributionChart"></canvas>
                </div>
                <div style="display: flex; justify-content: center; gap: 16px; margin-top: 16px;">
                    <?php foreach ($studentDistributionData as $entry): ?>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <div style="width: 12px; height: 12px; border-radius: 50%; background: <?php echo $entry['color']; ?>;"></div>
                            <span style="font-size: 14px; color: #6b7280;"><?php echo $entry['name']; ?> <?php echo $entry['value']; ?>%</span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Student Enrollment Trends Chart -->
            <div style="background: white; border-radius: 12px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); padding: 24px;">
                <h3 style="font-size: 18px; font-weight: 500; color: #111827; margin: 0 0 16px 0;">Student Enrollment Trends</h3>
                <div style="height: 256px; position: relative;">
                    <canvas id="enrollmentTrendsChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Additional Dashboard Sections -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px;">
            <!-- Recent Activities -->
            <div style="background: white; border-radius: 12px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); padding: 24px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
                    <h3 style="font-size: 18px; font-weight: 600; color: #111827; margin: 0;">Recent Activities</h3>
                    <a href="?tab=activities" style="font-size: 14px; color: #9333ea; font-weight: 500; text-decoration: none;">View All →</a>
                </div>
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <?php if (!empty($recentActivities)): ?>
                        <?php foreach ($recentActivities as $index => $activity): ?>
                            <div onclick="window.location.href='?tab=activities'" style="display: flex; align-items: center; gap: 12px; padding: 12px; background: linear-gradient(to right, #eff6ff, #f3e8ff); border-radius: 8px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.boxShadow='0 4px 6px -1px rgba(0,0,0,0.1)'" onmouseout="this.style.boxShadow='none'">
                                <div style="width: 8px; height: 8px; border-radius: 50%; background: <?php echo $index === 0 ? '#3b82f6' : ($index === 1 ? '#10b981' : '#8b5cf6'); ?>;"></div>
                                <div style="flex: 1; min-width: 0;">
                                    <p style="font-size: 14px; font-weight: 500; color: #111827; margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo htmlspecialchars($activity['title']); ?></p>
                                    <p style="font-size: 12px; color: #6b7280; margin: 4px 0 0 0;"><?php echo htmlspecialchars($activity['timeAgo']); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div style="text-align: center; padding: 32px 0;">
                            <span style="font-size: 48px; color: #d1d5db; display: block; margin-bottom: 8px;">📅</span>
                            <p style="font-size: 14px; color: #6b7280; margin: 0 0 8px 0;">No recent activities</p>
                            <a href="?tab=activities" style="font-size: 14px; color: #9333ea; font-weight: 500; text-decoration: none;">Add Activity</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Actions -->
            <div style="background: white; border-radius: 12px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); padding: 24px;">
                <h3 style="font-size: 18px; font-weight: 600; color: #111827; margin: 0 0 16px 0;">Quick Actions</h3>
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px;">
                    <button onclick="window.location.href='?tab=activities'" style="padding: 16px; border: 2px solid #e9d5ff; border-radius: 8px; background: white; text-align: left; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='#faf5ff'; this.style.borderColor='#c084fc'; this.style.transform='scale(1.05)'" onmouseout="this.style.background='white'; this.style.borderColor='#e9d5ff'; this.style.transform='scale(1)'">
                        <span style="font-size: 24px; display: block; margin-bottom: 8px;">📅</span>
                        <p style="font-size: 14px; font-weight: 600; color: #111827; margin: 0 0 4px 0;">Add Event</p>
                        <p style="font-size: 12px; color: #6b7280; margin: 0;">Create activity</p>
                    </button>
                    <button onclick="window.location.href='?tab=teachers'" style="padding: 16px; border: 2px solid #bbf7d0; border-radius: 8px; background: white; text-align: left; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='#f0fdf4'; this.style.borderColor='#86efac'; this.style.transform='scale(1.05)'" onmouseout="this.style.background='white'; this.style.borderColor='#bbf7d0'; this.style.transform='scale(1)'">
                        <span style="font-size: 24px; display: block; margin-bottom: 8px;">👥</span>
                        <p style="font-size: 14px; font-weight: 600; color: #111827; margin: 0 0 4px 0;">Add Teacher</p>
                        <p style="font-size: 12px; color: #6b7280; margin: 0;">New staff member</p>
                    </button>
                    <button onclick="window.location.href='?tab=facilities'" style="padding: 16px; border: 2px solid #bfdbfe; border-radius: 8px; background: white; text-align: left; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='#eff6ff'; this.style.borderColor='#93c5fd'; this.style.transform='scale(1.05)'" onmouseout="this.style.background='white'; this.style.borderColor='#bfdbfe'; this.style.transform='scale(1)'">
                        <span style="font-size: 24px; display: block; margin-bottom: 8px;">🏢</span>
                        <p style="font-size: 14px; font-weight: 600; color: #111827; margin: 0 0 4px 0;">Add Facility</p>
                        <p style="font-size: 12px; color: #6b7280; margin: 0;">New facility</p>
                    </button>
                    <button onclick="window.location.href='?tab=news'" style="padding: 16px; border: 2px solid #fce7f3; border-radius: 8px; background: white; text-align: left; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='#fdf2f8'; this.style.borderColor='#f9a8d4'; this.style.transform='scale(1.05)'" onmouseout="this.style.background='white'; this.style.borderColor='#fce7f3'; this.style.transform='scale(1)'">
                        <span style="font-size: 24px; display: block; margin-bottom: 8px;">📰</span>
                        <p style="font-size: 14px; font-weight: 600; color: #111827; margin: 0 0 4px 0;">Create News</p>
                        <p style="font-size: 12px; color: #6b7280; margin: 0;">Post announcement</p>
                    </button>
                </div>
            </div>

            <!-- School Statistics -->
            <div style="background: white; border-radius: 12px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); padding: 24px;">
                <h3 style="font-size: 18px; font-weight: 600; color: #111827; margin: 0 0 16px 0;">School Statistics</h3>
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <div onclick="window.location.href='?tab=teachers'" style="display: flex; justify-content: space-between; align-items: center; padding: 16px; background: linear-gradient(to right, #f0fdf4, #eff6ff); border-radius: 8px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.boxShadow='0 4px 6px -1px rgba(0,0,0,0.1)'" onmouseout="this.style.boxShadow='none'">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <span style="font-size: 20px;">👥</span>
                            <span style="font-size: 14px; font-weight: 500; color: #374151;">Teachers</span>
                        </div>
                        <span style="font-size: 20px; font-weight: bold; color: #10b981;"><?php echo $teachersCount; ?></span>
                    </div>
                    <div onclick="window.location.href='?tab=facilities'" style="display: flex; justify-content: space-between; align-items: center; padding: 16px; background: linear-gradient(to right, #faf5ff, #fdf2f8); border-radius: 8px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.boxShadow='0 4px 6px -1px rgba(0,0,0,0.1)'" onmouseout="this.style.boxShadow='none'">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <span style="font-size: 20px;">🏢</span>
                            <span style="font-size: 14px; font-weight: 500; color: #374151;">Facilities</span>
                        </div>
                        <span style="font-size: 20px; font-weight: bold; color: #8b5cf6;"><?php echo $facilitiesCount; ?></span>
                    </div>
                    <div onclick="window.location.href='?tab=testimonials'" style="display: flex; justify-content: space-between; align-items: center; padding: 16px; background: linear-gradient(to right, #fefce8, #fff7ed); border-radius: 8px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.boxShadow='0 4px 6px -1px rgba(0,0,0,0.1)'" onmouseout="this.style.boxShadow='none'">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <span style="font-size: 20px;">⭐</span>
                            <span style="font-size: 14px; font-weight: 500; color: #374151;">Testimonials</span>
                        </div>
                        <span style="font-size: 20px; font-weight: bold; color: #eab308;"><?php echo $testimonialsCount; ?></span>
                    </div>
                    <div onclick="window.location.href='?tab=news'" style="display: flex; justify-content: space-between; align-items: center; padding: 16px; background: linear-gradient(to right, #eff6ff, #eef2ff); border-radius: 8px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.boxShadow='0 4px 6px -1px rgba(0,0,0,0.1)'" onmouseout="this.style.boxShadow='none'">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <span style="font-size: 20px;">📰</span>
                            <span style="font-size: 14px; font-weight: 500; color: #374151;">News Articles</span>
                        </div>
                        <span style="font-size: 20px; font-weight: bold; color: #3b82f6;"><?php echo $newsCount; ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Student Distribution Pie Chart
const ctx1 = document.getElementById('studentDistributionChart');
if (ctx1) {
    new Chart(ctx1, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode(array_column($studentDistributionData, 'name')); ?>,
            datasets: [{
                data: <?php echo json_encode(array_column($studentDistributionData, 'value')); ?>,
                backgroundColor: <?php echo json_encode(array_column($studentDistributionData, 'color')); ?>,
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: '#fff',
                    borderColor: '#e5e7eb',
                    borderWidth: 1,
                    borderRadius: 8,
                    boxShadow: '0 4px 6px -1px rgba(0, 0, 0, 0.1)'
                }
            },
            cutout: '60%'
        }
    });
}

// Enrollment Trends Line Chart
const ctx2 = document.getElementById('enrollmentTrendsChart');
if (ctx2) {
    new Chart(ctx2, {
        type: 'line',
        data: {
            labels: <?php echo json_encode(array_column($monthlyEnrollmentData, 'month')); ?>,
            datasets: [{
                label: 'Enrollment',
                data: <?php echo json_encode(array_column($monthlyEnrollmentData, 'amount')); ?>,
                borderColor: '#10B981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointBackgroundColor: '#10B981',
                pointBorderColor: '#10B981',
                pointBorderWidth: 2,
                pointHoverRadius: 6,
                pointHoverBackgroundColor: '#10B981',
                pointHoverBorderColor: '#10B981',
                pointHoverBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: '#fff',
                    borderColor: '#e5e7eb',
                    borderWidth: 1,
                    borderRadius: 8,
                    boxShadow: '0 4px 6px -1px rgba(0, 0, 0, 0.1)'
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: '#6b7280',
                        font: {
                            size: 12
                        }
                    }
                },
                y: {
                    grid: {
                        color: '#f0f0f0',
                        borderDash: [3, 3]
                    },
                    ticks: {
                        color: '#6b7280',
                        font: {
                            size: 12
                        }
                    }
                }
            }
        }
    });
}
</script>
