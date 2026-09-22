<?php
session_start();
include "../config/db.php";
include "../config/lang.php"; // Hooks into your dictionary mapping layer

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Troubleshooting Manuals - RMS Portal</title>
    <style>
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --dark-bg: #090d16;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --border-color: rgba(255, 255, 255, 0.08);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--dark-bg);
            background-image: 
                radial-gradient(at 0% 0%, rgba(79, 70, 229, 0.12) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(13, 148, 136, 0.08) 0px, transparent 50%);
            color: var(--text-main);
            height: 100vh;
            width: 100vw;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            overflow: hidden;
        }

        /* NEW CONCEPT: Re-centered compact glassmorphism panel matrix */
        .support-wrapper {
            width: 100%;
            max-width: 600px;
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--border-color);
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            display: flex;
            flex-direction: column;
        }

        .header-section {
            margin-bottom: 25px;
        }

        .header-section h2 {
            font-size: 28px;
            font-weight: 800;
            letter-spacing: -0.5px;
            margin-bottom: 8px;
            background: linear-gradient(to right, #ffffff, #cbd5e1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .header-section p {
            color: var(--text-muted);
            font-size: 15px;
            line-height: 1.5;
        }

        .search-container {
            margin-bottom: 20px;
        }

        .search-container input {
            width: 100%;
            padding: 16px 18px;
            font-size: 15px;
            border: 1px solid var(--border-color);
            background: rgba(255, 255, 255, 0.03);
            border-radius: 14px;
            color: white;
            outline: none;
            transition: var(--transition);
        }

        .search-container input:focus {
            border-color: rgba(99, 102, 241, 0.4);
            background: rgba(255, 255, 255, 0.05);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15);
        }

        .manuals-accordion {
            display: flex;
            flex-direction: column;
            gap: 12px;
            max-height: 380px;
            overflow-y: auto;
            padding-right: 5px;
        }

        /* Custom Scrollbar configuration for clean rendering */
        .manuals-accordion::-webkit-scrollbar { width: 6px; }
        .manuals-accordion::-webkit-scrollbar-track { background: transparent; }
        .manuals-accordion::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }

        .manual-card {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid var(--border-color);
            border-radius: 14px;
            overflow: hidden;
            transition: var(--transition);
        }

        .manual-trigger {
            padding: 18px 20px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            user-select: none;
        }

        .manual-trigger h4 { font-size: 15px; font-weight: 700; color: #ffffff; }
        .manual-trigger span.badge { font-size: 11px; background: rgba(99, 102, 241, 0.15); color: #818cf8; padding: 4px 10px; border-radius: 20px; font-weight: 700; text-transform: uppercase; }

        .manual-content {
            padding: 0 20px;
            max-height: 0;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0, 1, 0, 1);
            color: var(--text-muted);
            font-size: 14px;
            line-height: 1.6;
        }

        .manual-card.open { border-color: rgba(99, 102, 241, 0.3); background: rgba(255, 255, 255, 0.03); }
        .manual-card.open .manual-content {
            padding: 18px 20px;
            max-height: 500px;
            border-top: 1px solid var(--border-color);
            transition: all 0.3s cubic-bezier(1, 0, 1, 0);
        }

        .manual-content ol { padding-left: 20px; margin-top: 8px; }
        .manual-content ol li { margin-bottom: 6px; }

        .card-footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
            text-align: center;
        }

        .card-footer a {
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: var(--transition);
        }

        .card-footer a:hover { color: white; }
    </style>
</head>
<body>

<div class="support-wrapper">
    
    <div class="header-section">
        <h2>Troubleshooting Manuals</h2>
        <p>Review step-by-step diagnostic procedures for unexpected facility issues instantly.</p>
    </div>

    <div class="search-container">
        <input type="text" id="manualSearch" placeholder="Search operational manuals (e.g., pipe leak, power)...">
    </div>

    <div class="manuals-accordion" id="accordionContainer">
        
        <div class="manual-card">
            <div class="manual-trigger">
                <h4>Emergency Water Leak / Burst Pipe</h4>
                <span class="badge">Plumbing</span>
            </div>
            <div class="manual-content">
                <p>If an infrastructure piping asset breaks or leaks uncontrollably, take these immediate containment steps before support arrives:</p>
                <ol>
                    <li>Locate your main estate gate valve (usually behind your bathroom layout or outside your main terminal entry gate) and rotate clockwise to shut off supply pressure.</li>
                    <li>Turn on all low-level taps to quickly drain residual fluid out of the plumbing system lines safely.</li>
                    <li>Clear out electronics or physical assets directly underneath the leakage zone to minimize building water damage.</li>
                </ol>
            </div>
        </div>

        <div class="manual-card">
            <div class="manual-trigger">
                <h4>Partial or Total Power System Outage</h4>
                <span class="badge">Electrical</span>
            </div>
            <div class="manual-content">
                <p>When sub-appliances trigger circuit power drops or structural blackouts occur unexpectedly:</p>
                <ol>
                    <li>Locate your local consumer breaker board box mounted inside the unit.</li>
                    <li>Check if the main safety RCD switch flipped downward (tripped status).</li>
                    <li>Unplug any newly connected hardware appliances, flip the breaker element upward, and check if power levels normalize safely.</li>
                </ol>
            </div>
        </div>

        <div class="manual-card">
            <div class="manual-trigger">
                <h4>Wall Paint Flaking & Wall Dampness</h4>
                <span class="badge">Painting</span>
            </div>
            <div class="manual-content">
                <p>Dealing with coat deterioration, surface mold, or structural surface flaking variables:</p>
                <ol>
                    <li>Avoid applying immediate fresh surface coats over damp structural areas; this seals moisture in and escalates block decay.</li>
                    <li>Use a dry cloth to brush away immediate loose flakes to keep wall scaling clear.</li>
                    <li>Observe if the damp spot originates directly below an active pipeline utility valve.</li>
                </ol>
            </div>
        </div>

        <div class="manual-card">
            <div class="manual-trigger">
                <h4>Solar Inverter / Grid Connection Errors</h4>
                <span class="badge">Solar Tech</span>
            </div>
            <div class="manual-content">
                <p>When roof solar array arrays show connection fault codes or cut off emergency backup battery arrays:</p>
                <ol>
                    <li>Check the digital display indicator board for the specific numerical fault code parameter label.</li>
                    <li>Do not attempt to unscrew battery terminals manually due to high-voltage line danger limits.</li>
                    <li>Perform a clean system toggle restart by isolating the solar breaker input switch for 60 seconds before flipping it back on.</li>
                </ol>
            </div>
        </div>

    </div>

    <div class="card-footer">
        <a href="dashboard.php">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" style="transform: scaleX(-1);"><polyline points="9 18 15 12 9 6"></polyline></svg>
            Return to System Dashboard
        </a>
    </div>

</div>

<script>
    // 1. Accordion Dropdown slide dynamics
    document.querySelectorAll('.manual-trigger').forEach(trigger => {
        trigger.addEventListener('click', () => {
            const currentCard = trigger.parentElement;
            const isOpen = currentCard.classList.contains('open');
            
            // Close other cards to maintain clean interface layout
            document.querySelectorAll('.manual-card').forEach(card => card.classList.remove('open'));
            
            // Toggle active card visibility parameters
            if (!isOpen) {
                currentCard.classList.add('open');
            }
        });
    });

    // 2. JavaScript Live Key Filter Module
    const searchField = document.getElementById('manualSearch');
    const manualCards = document.querySelectorAll('.manual-card');

    searchField.addEventListener('input', function() {
        const queryText = this.value.toLowerCase().trim();

        manualCards.forEach(card => {
            const titleContent = card.querySelector('h4').textContent.toLowerCase();
            const bodyContent = card.querySelector('.manual-content').textContent.toLowerCase();
            const categoryLabel = card.querySelector('.badge').textContent.toLowerCase();

            if (titleContent.includes(queryText) || bodyContent.includes(queryText) || categoryLabel.includes(queryText)) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
                card.classList.remove('open');
            }
        });
    });
</script>

</body>
</html>