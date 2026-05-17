<?php
session_start();
include_once "config/database.php";

$conn = getConnection();

// Get all parking spaces with their current status
$sql = "SELECT p.*, 
        (SELECT COUNT(*) FROM reservations r 
         WHERE r.space_id = p.space_id 
         AND r.status IN ('active', 'pending')
         AND NOW() BETWEEN r.start_time AND r.end_time) as current_occupancy
        FROM parking_spaces p
        ORDER BY p.location, p.space_name";
$result = $conn->query($sql);
$spaces = $result->fetch_all(MYSQLI_ASSOC);

// Group spaces by location
$locations = [];
foreach ($spaces as $space) {
    $locations[$space['location']][] = $space;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View All Spaces - SmartPark</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4CAF50;
            --primary-dark: #388E3C;
            --primary-light: rgba(76, 175, 80, 0.1);
            --text-dark: #333;
            --text-light: #666;
            --white: #fff;
            --gray-light: #f5f5f5;
            --shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            --available: #4CAF50;
            --occupied: #f44336;
            --selected: #2196F3;
            --hover: #81C784;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--gray-light);
            color: var(--text-dark);
            line-height: 1.6;
        }

        .spaces-wrapper {
            padding-top: 80px;
            min-height: 100vh;
        }

        .spaces-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }

        .spaces-header {
            background: var(--white);
            padding: 2rem;
            border-radius: 10px;
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
            text-align: center;
        }

        .spaces-header h1 {
            margin: 0;
            font-size: 2rem;
            color: var(--text-dark);
        }

        .spaces-header p {
            margin: 0.5rem 0 0;
            color: var(--text-light);
        }

        .location-section {
            background: var(--white);
            border-radius: 10px;
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
            overflow: hidden;
        }

        .location-header {
            background: var(--primary-light);
            padding: 1.5rem;
            border-bottom: 1px solid var(--gray-light);
        }

        .location-header h2 {
            margin: 0;
            color: var(--text-dark);
            font-size: 1.5rem;
        }

        .seating-layout {
            padding: 2rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2rem;
        }

        .screen {
            width: 80%;
            height: 20px;
            background: var(--primary-color);
            border-radius: 10px;
            margin-bottom: 2rem;
            position: relative;
        }

        .screen::before {
            content: 'ENTRANCE';
            position: absolute;
            top: -30px;
            left: 50%;
            transform: translateX(-50%);
            color: var(--text-light);
            font-size: 0.9rem;
        }

        .seats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(60px, 1fr));
            gap: 1rem;
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
        }

        .seat {
            aspect-ratio: 1;
            background: var(--available);
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-weight: 500;
            font-size: 0.9rem;
        }

        .seat:hover {
            transform: scale(1.1);
            background: var(--hover);
        }

        .seat.occupied {
            background: var(--occupied);
            cursor: not-allowed;
        }

        .seat.selected {
            background: var(--selected);
        }

        .seat-info {
            position: absolute;
            top: -40px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--white);
            padding: 0.5rem;
            border-radius: 5px;
            box-shadow: var(--shadow);
            display: none;
            white-space: nowrap;
            z-index: 10;
        }

        .seat:hover .seat-info {
            display: block;
        }

        .legend {
            display: flex;
            gap: 2rem;
            margin-top: 2rem;
            padding: 1rem;
            background: var(--white);
            border-radius: 5px;
            box-shadow: var(--shadow);
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .legend-color {
            width: 20px;
            height: 20px;
            border-radius: 3px;
        }

        .legend-available {
            background: var(--available);
        }

        .legend-occupied {
            background: var(--occupied);
        }

        .legend-selected {
            background: var(--selected);
        }

        .booking-actions {
            margin-top: 2rem;
            display: flex;
            justify-content: center;
            gap: 1rem;
        }

        .btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .btn i {
            font-size: 1.1rem;
        }

        .btn-primary {
            background: var(--primary-color);
            color: var(--white);
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .btn-primary:disabled {
            background: var(--text-light);
            cursor: not-allowed;
            transform: none;
        }

        .booking-summary {
            background: var(--white);
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: var(--shadow);
            margin-top: 2rem;
            display: none;
        }

        .booking-summary.active {
            display: block;
        }

        .summary-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--gray-light);
        }

        .summary-header h3 {
            margin: 0;
            color: var(--text-dark);
        }

        .summary-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .summary-item {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .summary-label {
            color: var(--text-light);
            font-size: 0.9rem;
        }

        .summary-value {
            font-weight: 500;
            color: var(--text-dark);
        }

        @media (max-width: 768px) {
            .seats-container {
                grid-template-columns: repeat(auto-fit, minmax(40px, 1fr));
            }

            .seat {
                font-size: 0.8rem;
            }

            .legend {
                flex-direction: column;
                gap: 1rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="spaces-wrapper">
        <div class="spaces-container">
            <div class="spaces-header">
                <h1>View All Spaces</h1>
                <p>Select your preferred parking space</p>
            </div>

            <?php if (empty($locations)): ?>
                <div class="location-section">
                    <div class="location-header">
                        <h2>No Parking Spaces Available</h2>
                    </div>
                    <div class="seating-layout">
                        <p>There are no parking spaces available at the moment. Please check back later.</p>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($locations as $location => $locationSpaces): ?>
                    <div class="location-section">
                        <div class="location-header">
                            <h2><?php echo htmlspecialchars($location); ?></h2>
                        </div>
                        <div class="seating-layout">
                            <div class="screen"></div>
                            <div class="seats-container">
                                <?php foreach ($locationSpaces as $space): ?>
                                    <div class="seat <?php echo $space['current_occupancy'] > 0 ? 'occupied' : ''; ?>" 
                                         data-space-id="<?php echo $space['space_id']; ?>"
                                         data-space-name="<?php echo htmlspecialchars($space['space_name']); ?>"
                                         data-price="<?php echo $space['price_per_hour']; ?>">
                                        <?php echo $space['space_name']; ?>
                                        <div class="seat-info">
                                            <div>Space: <?php echo htmlspecialchars($space['space_name']); ?></div>
                                            <div>Price: $<?php echo number_format($space['price_per_hour'], 2); ?>/hour</div>
                                            <div>Status: <?php echo $space['current_occupancy'] > 0 ? 'Occupied' : 'Available'; ?></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="legend">
                                <div class="legend-item">
                                    <div class="legend-color legend-available"></div>
                                    <span>Available</span>
                                </div>
                                <div class="legend-item">
                                    <div class="legend-color legend-occupied"></div>
                                    <span>Occupied</span>
                                </div>
                                <div class="legend-item">
                                    <div class="legend-color legend-selected"></div>
                                    <span>Selected</span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="booking-summary">
                    <div class="summary-header">
                        <h3>Booking Summary</h3>
                        <button class="btn btn-primary" id="proceedToBook">
                            <i class="fas fa-check"></i>
                            Proceed to Book
                        </button>
                    </div>
                    <div class="summary-content">
                        <div class="summary-item">
                            <span class="summary-label">Selected Space</span>
                            <span class="summary-value" id="selectedSpaceName">-</span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Price per Hour</span>
                            <span class="summary-value" id="selectedSpacePrice">-</span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Location</span>
                            <span class="summary-value" id="selectedSpaceLocation">-</span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const seats = document.querySelectorAll('.seat:not(.occupied)');
            const bookingSummary = document.querySelector('.booking-summary');
            const selectedSpaceName = document.getElementById('selectedSpaceName');
            const selectedSpacePrice = document.getElementById('selectedSpacePrice');
            const selectedSpaceLocation = document.getElementById('selectedSpaceLocation');
            const proceedButton = document.getElementById('proceedToBook');
            let selectedSpace = null;

            seats.forEach(seat => {
                seat.addEventListener('click', function() {
                    // Remove selection from previously selected seat
                    if (selectedSpace) {
                        selectedSpace.classList.remove('selected');
                    }

                    // Select new seat
                    this.classList.add('selected');
                    selectedSpace = this;

                    // Update booking summary
                    const spaceName = this.dataset.spaceName;
                    const price = this.dataset.price;
                    const location = this.closest('.location-section').querySelector('.location-header h2').textContent;

                    selectedSpaceName.textContent = spaceName;
                    selectedSpacePrice.textContent = `$${parseFloat(price).toFixed(2)}/hour`;
                    selectedSpaceLocation.textContent = location;

                    // Show booking summary
                    bookingSummary.classList.add('active');
                });
            });

            proceedButton.addEventListener('click', function() {
                if (selectedSpace) {
                    const spaceId = selectedSpace.dataset.spaceId;
                    const spaceName = selectedSpace.dataset.spaceName;
                    const location = selectedSpace.dataset.location;
                    const price = selectedSpace.dataset.price;
                    
                    // Create and submit the booking form
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = 'user/book_space.php';

                    // Add form fields
                    const fields = {
                        'space_id': spaceId,
                        'space_name': spaceName,
                        'location': location,
                        'price': price
                    };

                    for (const [name, value] of Object.entries(fields)) {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = name;
                        input.value = value;
                        form.appendChild(input);
                    }

                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });
    </script>
</body>
</html> 