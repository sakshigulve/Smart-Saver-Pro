<?php
include 'includes/config.php';
checkLogin();
$user_id = $_SESSION['user_id'];

// --- 1. ACTION HANDLERS ---
if (isset($_GET['delete_trans'])) {
    $stmt = $pdo->prepare("DELETE FROM transactions WHERE id = ? AND user_id = ?");
    $stmt->execute([$_GET['delete_trans'], $user_id]);
    header("Location: dashboard.php"); exit();
}
if (isset($_GET['delete_goal'])) {
    $stmt = $pdo->prepare("DELETE FROM goals WHERE id = ? AND user_id = ?");
    $stmt->execute([$_GET['delete_goal'], $user_id]);
    header("Location: dashboard.php"); exit();
}
if (isset($_GET['pay_bill'])) {
    $stmt = $pdo->prepare("UPDATE bills SET status = 'paid' WHERE id = ? AND user_id = ?");
    $stmt->execute([$_GET['pay_bill'], $user_id]);
    header("Location: dashboard.php"); exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_transaction'])) {
        $stmt = $pdo->prepare("INSERT INTO transactions (user_id, category_id, amount, description, date) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $_POST['category'], $_POST['amount'], $_POST['desc'], $_POST['date']]);
    } elseif (isset($_POST['add_goal'])) {
        $stmt = $pdo->prepare("INSERT INTO goals (user_id, goal_name, target_amount) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $_POST['goal_name'], $_POST['target_amount']]);
    } elseif (isset($_POST['add_bill'])) {
        $stmt = $pdo->prepare("INSERT INTO bills (user_id, bill_name, amount, due_date) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $_POST['bill_name'], $_POST['amount'], $_POST['due_date']]);
    }
    header("Location: dashboard.php"); exit();
}

// --- 2. DATA CALCULATIONS ---
$inc = $pdo->prepare("SELECT SUM(amount) FROM transactions t JOIN categories c ON t.category_id = c.id WHERE t.user_id = ? AND c.type = 'income'");
$inc->execute([$user_id]);
$total_income = $inc->fetchColumn() ?: 0;

$exp = $pdo->prepare("SELECT SUM(amount) FROM transactions t JOIN categories c ON t.category_id = c.id WHERE t.user_id = ? AND c.type = 'expense'");
$exp->execute([$user_id]);
$total_expense = $exp->fetchColumn() ?: 0;
$savings = $total_income - $total_expense;

// Health Score Logic
$score = ($total_income > 0) ? round(100 - (($total_expense / $total_income) * 100)) : 0;
$score = max(0, min(100, $score));
$health_color = ($score > 75) ? '#00ff87' : (($score > 40) ? '#f1c40f' : '#ff4b2b');

// Data Lists
$recent_trans = $pdo->prepare("SELECT t.*, c.name as cat_name, c.type FROM transactions t JOIN categories c ON t.category_id = c.id WHERE t.user_id = ? ORDER BY date DESC LIMIT 5");
$recent_trans->execute([$user_id]);
$trans_list = $recent_trans->fetchAll();

$bills_stmt = $pdo->prepare("SELECT * FROM bills WHERE user_id = ? AND status = 'unpaid' ORDER BY due_date ASC");
$bills_stmt->execute([$user_id]);
$unpaid_bills = $bills_stmt->fetchAll();

$goals_stmt = $pdo->prepare("SELECT * FROM goals WHERE user_id = ?");
$goals_stmt->execute([$user_id]);
$goals = $goals_stmt->fetchAll();

// Academy Content
$videos = [
    ['id' => '0CaA5K5EsN8', 'title' => 'Master the 50/30/20 Budgeting Rule', 'desc' => 'Learn how to split your income for maximum savings.'],
    ['id' => 'uQxUM5D6-Ow', 'title' => 'The Power of Compound Interest', 'desc' => 'See how small savings grow into millions over time.'],
    ['id' => '4j2emMn7UaI', 'title' => '10 Secrets to Saving Money Daily', 'desc' => 'Easy life hacks to stop overspending.'],
    ['id' => '4XZIv4__sQA', 'title' => 'Financial Literacy 101', 'desc' => 'The basics every student must know about money.']
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Smart Saver Pro | Advanced Dashboard</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-grid">
        <!-- Sidebar -->
        <div class="sidebar">
            <div style="text-align: center; margin-bottom: 40px;">
    <?php 
    // Check if user has a profile pic, otherwise use a default UI avatar
    $user_pic = $_SESSION['profile_pic'];
    if (!file_exists($user_pic) || empty($user_pic)) {
        $user_pic = "https://ui-avatars.com/api/?name=" . urlencode($_SESSION['username']) . "&background=00d2ff&color=fff";
    }
    ?>
    <img src="<?php echo $user_pic; ?>" style="width:80px; height:80px; border-radius:50%; border: 2px solid var(--primary); object-fit: cover; background: #1e293b;">
    <h3 style="margin-top:15px;"><?php echo $_SESSION['username']; ?></h3>
</div>
            <div class="sidebar-nav">
                <a href="dashboard.php" class="active"><p><i class="fa fa-th-large"></i> Dashboard</p></a>
                <a href="#"><p><i class="fa fa-exchange-alt"></i> Transactions</p></a>
                <a href="#"><p><i class="fa fa-receipt"></i> Bills & Subs</p></a>
                <a href="logout.php"><p style="color:var(--danger)"><i class="fa fa-sign-out-alt"></i> Logout</p></a>
            </div>
        </div>

        <div class="main-content">
            <!-- AI Forecast -->
            <div class="advice-card">
                <h3><i class="fa fa-brain"></i> Financial Forecast</h3>
                <p>Health Score: <strong><?php echo $score; ?>%</strong>. Projected annual savings: <strong>₹<?php echo number_format($savings * 12); ?></strong>.</p>
            </div>

            <!-- Stats Row -->
            <div class="stats-container">
                <div class="card stat-card" style="text-align:center;">
                    <h3>Health Score</h3>
                    <p style="color: <?php echo $health_color; ?>"><?php echo $score; ?>%</p>
                </div>
                <div class="card stat-card"><h3>Income</h3><p style="color:var(--success)">₹<?php echo number_format($total_income); ?></p></div>
                <div class="card stat-card"><h3>Expenses</h3><p style="color:var(--danger)">₹<?php echo number_format($total_expense); ?></p></div>
            </div>

            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">
                <!-- Left Column -->
                <div>
                    <div class="card">
                        <h3><i class="fa fa-chart-line"></i> Cashflow Visualization</h3>
                        <canvas id="mainChart" height="140"></canvas>
                    </div>

                    <div class="card">
                        <h3>Recent Activity</h3>
                        <table>
                            <thead><tr><th>Date</th><th>Category</th><th>Amount</th><th></th></tr></thead>
                            <tbody>
                                <?php foreach($trans_list as $t): ?>
                                <tr>
                                    <td><?php echo $t['date']; ?></td>
                                    <td><?php echo $t['cat_name']; ?></td>
                                    <td style="color:<?php echo $t['type']=='income'?'var(--success)':'var(--danger)'; ?>; font-weight:bold;">₹<?php echo number_format($t['amount']); ?></td>
                                    <td><a href="?delete_trans=<?php echo $t['id']; ?>" style="color:var(--danger); font-size:0.7rem;">Delete</a></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Right Column (RESTORED BILLS SECTION) -->
                <div>
                    <!-- 1. Saving Goals -->
                    <div class="card" style="margin-bottom: 25px;">
                        <h3>Saving Goals</h3>
                        <?php foreach($goals as $g): $p = $g['target_amount']>0 ? min(100, ($savings/$g['target_amount'])*100) : 0; ?>
                        <div style="margin-bottom:15px;">
                            <div style="display:flex; justify-content:space-between; font-size:0.8rem;">
                                <span><?php echo $g['goal_name']; ?></span>
                                <a href="?delete_goal=<?php echo $g['id']; ?>" style="color:var(--danger)">X</a>
                            </div>
                            <div class="progress-bar"><div class="progress-fill" style="width:<?php echo $p; ?>%"></div></div>
                        </div>
                        <?php endforeach; ?>
                        <form method="POST"><input type="text" name="goal_name" placeholder="Goal Name" required><input type="number" name="target_amount" placeholder="Target ₹" required><button type="submit" name="add_goal" class="btn">Add Goal</button></form>
                    </div>

                    <!-- 2. Restored Bills & Subscriptions -->
                    <div class="card" style="margin-bottom: 25px;">
                        <h3>Upcoming Bills</h3>
                        <?php foreach($unpaid_bills as $b): ?>
                        <div style="display:flex; justify-content:space-between; font-size:0.8rem; margin-bottom:10px;">
                            <span><?php echo $b['bill_name']; ?> (₹<?php echo number_format($b['amount']); ?>)</span>
                            <a href="?pay_bill=<?php echo $b['id']; ?>" style="color:var(--success); text-decoration:none;">Pay Now</a>
                        </div>
                        <?php endforeach; ?>
                        <form method="POST" style="border-top: 1px solid var(--glass); padding-top:15px; margin-top:10px;">
                            <input type="text" name="bill_name" placeholder="Bill (WiFi/Rent)" required>
                            <input type="number" name="amount" placeholder="₹ Amount" required>
                            <input type="date" name="due_date" required>
                            <button type="submit" name="add_bill" class="btn">Add Bill</button>
                        </form>
                    </div>

                    <!-- 3. Quick Add Transaction -->
                    <div class="card">
                        <h3>Add Transaction</h3>
                        <form method="POST">
                            <select name="category" required>
                                <option value="" disabled selected>Category</option>
                                <?php $cats = $pdo->query("SELECT * FROM categories")->fetchAll(); foreach($cats as $c) echo "<option value='{$c['id']}'>{$c['name']}</option>"; ?>
                            </select>
                            <input type="number" name="amount" placeholder="Amount ₹" required>
                            <input type="date" name="date" value="<?php echo date('Y-m-d'); ?>">
                            <button type="submit" name="add_transaction" class="btn">Save Entry</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Full Width Cinema Academy -->
            <div class="card academy-section">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                    <h3 style="margin:0;"><i class="fa fa-graduation-cap"></i> Financial Academy Pro</h3>
                    <span id="playing-now" style="color:var(--primary); font-size:0.8rem; font-weight:bold;">NOW PLAYING: <?php echo $videos[0]['title']; ?></span>
                </div>
                <div class="cinema-container">
                    <div class="main-player-wrapper">
                        <iframe id="videoPlayer" src="https://www.youtube.com/embed/<?php echo $videos[0]['id']; ?>?rel=0&modestbranding=1" allowfullscreen></iframe>
                    </div>
                    <div class="cinema-playlist">
                        <?php foreach($videos as $index => $v): ?>
                        <div class="cinema-item <?php echo $index==0?'active':''; ?>" onclick="switchVideo('<?php echo $v['id']; ?>', '<?php echo addslashes($v['title']); ?>', this)">
                            <img src="https://img.youtube.com/vi/<?php echo $v['id']; ?>/mqdefault.jpg">
                            <div class="cinema-info">
                                <h4><?php echo $v['title']; ?></h4>
                                <p><?php echo $v['desc']; ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('mainChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Income', 'Expenses', 'Savings'],
                datasets: [{
                    data: [<?php echo "$total_income, $total_expense, $savings"; ?>],
                    backgroundColor: ['#00ff87', '#ff4b2b', '#00d2ff'],
                    borderRadius: 10
                }]
            },
            options: { 
                plugins: { legend: { display: false } },
                scales: { 
                    y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#94a3b8' } },
                    x: { ticks: { color: '#94a3b8' } }
                } 
            }
        });

        function switchVideo(id, title, el) {
            document.getElementById('videoPlayer').src = "https://www.youtube.com/embed/" + id + "?autoplay=1&rel=0";
            document.getElementById('playing-now').innerText = "NOW PLAYING: " + title;
            document.querySelectorAll('.cinema-item').forEach(item => item.classList.remove('active'));
            el.classList.add('active');
        }
    </script>
</body>
</html>