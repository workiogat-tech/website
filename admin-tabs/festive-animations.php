<?php
/**
 * Festive Animations Tab
 * Match the design from the images with proper controls
 */

// Ensure table exists
try {
    $tableExists = $db->fetchOne("SHOW TABLES LIKE 'festive_animations_settings'");
    if (!$tableExists) {
        $db->query("CREATE TABLE IF NOT EXISTS festive_animations_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            enabled BOOLEAN DEFAULT FALSE,
            opacity DECIMAL(3,2) DEFAULT 0.9,
            speed DECIMAL(3,2) DEFAULT 0.6,
            size DECIMAL(3,2) DEFAULT 2.0,
            intensity DECIMAL(3,2) DEFAULT 1.0,
            colors TEXT,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }
} catch (Exception $e) {
    error_log("Festive animations table check error: " . $e->getMessage());
}

$festive = $db->fetchOne("SELECT * FROM festive_animations_settings WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?? [];

// Parse colors
$colors = [];
if (!empty($festive['colors'])) {
    if (is_string($festive['colors'])) {
        $colors = json_decode($festive['colors'], true) ?: [];
    } else {
        $colors = $festive['colors'];
    }
}
if (!is_array($colors)) {
    $colors = [];
}

// Default colors if empty
$defaultColors = ['#FFFFFF', '#87CEEB', '#87CEFA', '#00CED1', '#9370DB', '#FF69B4', '#90EE90'];
$availableColors = [
    ['name' => 'White', 'value' => '#FFFFFF'],
    ['name' => 'Light Blue', 'value' => '#87CEEB'],
    ['name' => 'Sky Blue', 'value' => '#87CEFA'],
    ['name' => 'Cyan', 'value' => '#00CED1'],
    ['name' => 'Purple', 'value' => '#9370DB'],
    ['name' => 'Pink', 'value' => '#FF69B4'],
    ['name' => 'Green', 'value' => '#90EE90'],
    ['name' => 'Yellow', 'value' => '#FFD700'],
];

$enabled = ($festive['enabled'] ?? false) ? true : false;
$opacity = floatval($festive['opacity'] ?? 0.9) * 100; // Convert to percentage
$speed = floatval($festive['speed'] ?? 0.6);
$size = floatval($festive['size'] ?? 2.0);
$intensity = floatval($festive['intensity'] ?? 1.0) * 100; // Convert to percentage
?>
<div class="content-card">
    <h2 style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px;">
        <span style="font-size: 28px;">❄️</span>
        <span>Festive Animations Control</span>
    </h2>
    
    <form method="POST" action="api/content/festive-animations.php" id="festiveForm" onsubmit="return handleFestiveFormSubmit(event, this);">
        
        <!-- Animation Settings Section -->
        <div style="background: linear-gradient(135deg, #e0f2fe, #dbeafe); padding: 25px; border-radius: 12px; border: 2px solid #0ea5e9; margin-bottom: 30px;">
            <h3 style="font-size: 20px; font-weight: 600; color: #333; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 24px;">❄️</span>
                <span>Animation Settings</span>
            </h3>
            
            <!-- Enable Toggle -->
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 20px; background: white; border-radius: 10px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <div>
                    <label style="font-size: 18px; font-weight: 600; color: #111827; display: block; margin-bottom: 5px;">Enable Festive Animations</label>
                    <p style="font-size: 14px; color: #6b7280; margin: 0;">Activate snowflake animations that fall across all website pages</p>
                </div>
                <label class="toggle-switch">
                    <input type="checkbox" 
                           name="enabled" 
                           id="enableAnimations"
                           <?php echo $enabled ? 'checked' : ''; ?>
                           onchange="updatePreview();">
                    <span class="toggle-slider"></span>
                </label>
            </div>
            
            <!-- Opacity Slider -->
            <div style="background: white; padding: 20px; border-radius: 10px; margin-bottom: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px;">
                    <label style="font-size: 16px; font-weight: 600; color: #111827;">Opacity</label>
                    <span id="opacityValue" style="background: #0ea5e9; color: white; padding: 4px 12px; border-radius: 20px; font-size: 14px; font-weight: 600;"><?php echo round($opacity); ?>%</span>
                </div>
                <p style="font-size: 13px; color: #6b7280; margin-bottom: 15px;">Control animation transparency (0% = invisible, 100% = fully visible)</p>
                <div style="position: relative;">
                    <input type="range" 
                           name="opacity_percent" 
                           id="opacitySlider"
                           min="0" 
                           max="100" 
                           step="1" 
                           value="<?php echo round($opacity); ?>"
                           style="width: 100%; height: 8px; border-radius: 4px; background: #e5e7eb; outline: none; -webkit-appearance: none;"
                           oninput="document.getElementById('opacityValue').textContent=this.value+'%'; document.getElementById('opacityInput').value=this.value/100; updatePreview();">
                    <div style="display: flex; justify-content: space-between; margin-top: 8px; font-size: 12px; color: #6b7280;">
                        <span>0%</span>
                        <span>50%</span>
                        <span>100%</span>
                    </div>
                </div>
                <input type="hidden" name="opacity" id="opacityInput" value="<?php echo $opacity / 100; ?>">
            </div>
            
            <!-- Animation Speed Slider -->
            <div style="background: white; padding: 20px; border-radius: 10px; margin-bottom: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px;">
                    <label style="font-size: 16px; font-weight: 600; color: #111827;">Animation Speed</label>
                    <span id="speedValue" style="background: #0ea5e9; color: white; padding: 4px 12px; border-radius: 20px; font-size: 14px; font-weight: 600;"><?php echo number_format($speed, 1); ?>x</span>
                </div>
                <p style="font-size: 13px; color: #6b7280; margin-bottom: 15px;">Control how fast the animations fall (0.5x = slow, 2.0x = fast)</p>
                <div style="position: relative;">
                    <input type="range" 
                           name="speed" 
                           id="speedSlider"
                           min="0.5" 
                           max="2.0" 
                           step="0.1" 
                           value="<?php echo $speed; ?>"
                           style="width: 100%; height: 8px; border-radius: 4px; background: #e5e7eb; outline: none; -webkit-appearance: none;"
                           oninput="document.getElementById('speedValue').textContent=parseFloat(this.value).toFixed(1)+'x'; updatePreview();">
                    <div style="display: flex; justify-content: space-between; margin-top: 8px; font-size: 12px; color: #6b7280;">
                        <span>0.5x (Slow)</span>
                        <span>1.0x (Normal)</span>
                        <span>2.0x (Fast)</span>
                    </div>
                </div>
            </div>
            
            <!-- Snowflake Size Slider -->
            <div style="background: white; padding: 20px; border-radius: 10px; margin-bottom: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px;">
                    <label style="font-size: 16px; font-weight: 600; color: #111827;">Snowflake Size</label>
                    <span id="sizeValue" style="background: #9370DB; color: white; padding: 4px 12px; border-radius: 20px; font-size: 14px; font-weight: 600;"><?php echo number_format($size, 1); ?>x</span>
                </div>
                <p style="font-size: 13px; color: #6b7280; margin-bottom: 15px;">Control the size of the snowflakes (0.5x = small, 2.0x = large)</p>
                <div style="position: relative;">
                    <input type="range" 
                           name="size" 
                           id="sizeSlider"
                           min="0.5" 
                           max="2.0" 
                           step="0.1" 
                           value="<?php echo $size; ?>"
                           style="width: 100%; height: 8px; border-radius: 4px; background: #e5e7eb; outline: none; -webkit-appearance: none;"
                           oninput="document.getElementById('sizeValue').textContent=parseFloat(this.value).toFixed(1)+'x'; updatePreview();">
                    <div style="display: flex; justify-content: space-between; margin-top: 8px; font-size: 12px; color: #6b7280;">
                        <span>0.5x (Small)</span>
                        <span>1.0x (Normal)</span>
                        <span>2.0x (Large)</span>
                    </div>
                </div>
            </div>
            
            <!-- Intensity Slider -->
            <div style="background: white; padding: 20px; border-radius: 10px; margin-bottom: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px;">
                    <label style="font-size: 16px; font-weight: 600; color: #111827;">Intensity</label>
                    <span id="intensityValue" style="background: #22c55e; color: white; padding: 4px 12px; border-radius: 20px; font-size: 14px; font-weight: 600;"><?php echo round($intensity); ?>%</span>
                </div>
                <p style="font-size: 13px; color: #6b7280; margin-bottom: 15px;">Control the density and brightness of animations (0% = sparse/dim, 100% = dense/bright)</p>
                <div style="position: relative;">
                    <input type="range" 
                           name="intensity_percent" 
                           id="intensitySlider"
                           min="0" 
                           max="100" 
                           step="1" 
                           value="<?php echo round($intensity); ?>"
                           style="width: 100%; height: 8px; border-radius: 4px; background: #e5e7eb; outline: none; -webkit-appearance: none;"
                           oninput="document.getElementById('intensityValue').textContent=this.value+'%'; document.getElementById('intensityInput').value=this.value/100; updatePreview();">
                    <div style="display: flex; justify-content: space-between; margin-top: 8px; font-size: 12px; color: #6b7280;">
                        <span>0% (Low)</span>
                        <span>50% (Medium)</span>
                        <span>100% (High)</span>
                    </div>
                </div>
                <input type="hidden" name="intensity" id="intensityInput" value="<?php echo $intensity / 100; ?>">
            </div>
        </div>
        
        <!-- Color Mix Section -->
        <div style="background: linear-gradient(135deg, #fef3c7, #fde68a); padding: 25px; border-radius: 12px; border: 2px solid #f59e0b; margin-bottom: 30px;">
            <h3 style="font-size: 20px; font-weight: 600; color: #333; margin-bottom: 15px;">Color Mix</h3>
            <p style="font-size: 14px; color: #6b7280; margin-bottom: 20px;">Select colors for the snowflakes. Multiple colors will be randomly mixed.</p>
            
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 20px;">
                <?php foreach ($availableColors as $color): 
                    $isSelected = in_array($color['value'], $colors);
                ?>
                    <div class="color-option" 
                         style="background: white; padding: 15px; border-radius: 12px; border: 2px solid <?php echo $isSelected ? $color['value'] : '#e5e7eb'; ?>; cursor: pointer; transition: all 0.3s; <?php echo $isSelected ? 'box-shadow: 0 4px 12px rgba(0,0,0,0.15);' : ''; ?>"
                         onclick="toggleColor('<?php echo $color['value']; ?>', this);">
                        <div style="display: flex; flex-direction: column; align-items: center; gap: 10px;">
                            <div style="width: 60px; height: 60px; border-radius: 50%; background: <?php echo $color['value']; ?>; border: 3px solid white; box-shadow: 0 2px 8px rgba(0,0,0,0.2);"></div>
                            <div style="text-align: center;">
                                <div style="font-weight: 600; color: #111827; margin-bottom: 4px;"><?php echo htmlspecialchars($color['name']); ?></div>
                                <?php if ($isSelected): ?>
                                    <div style="font-size: 12px; color: #22c55e; font-weight: 600;">✓ Selected</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div style="background: white; padding: 15px; border-radius: 10px;">
                <div style="font-size: 14px; font-weight: 600; color: #111827; margin-bottom: 10px;">
                    Selected Colors (<span id="selectedCount"><?php echo count($colors); ?></span>):
                </div>
                <div id="selectedColorsDisplay" style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <?php foreach ($colors as $colorValue): ?>
                        <div style="width: 30px; height: 30px; border-radius: 50%; background: <?php echo htmlspecialchars($colorValue); ?>; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.2);"></div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <input type="hidden" name="colors" id="colorsInput" value="<?php echo htmlspecialchars(json_encode($colors)); ?>">
        </div>
        
        <!-- Live Preview Section -->
        <div style="background: linear-gradient(135deg, #f0f9ff, #e0f2fe); padding: 25px; border-radius: 12px; border: 2px solid #0ea5e9; margin-bottom: 30px;">
            <h3 style="font-size: 20px; font-weight: 600; color: #333; margin-bottom: 15px; display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 24px;">❄️</span>
                <span>Live Preview</span>
            </h3>
            <p style="font-size: 14px; color: #6b7280; margin-bottom: 15px;">Changes are applied immediately. Navigate to any page to see the animations in action.</p>
            <div id="previewStatus" style="padding: 15px; background: white; border-radius: 8px; border-left: 4px solid <?php echo $enabled ? '#22c55e' : '#ef4444'; ?>;">
                <p style="margin: 0; font-weight: 600; color: #111827;">
                    <?php if ($enabled): ?>
                        <span style="color: #22c55e;">●</span> Animations are currently <strong>active</strong> across all pages
                    <?php else: ?>
                        <span style="color: #ef4444;">●</span> Animations are currently <strong>inactive</strong>
                    <?php endif; ?>
                </p>
            </div>
        </div>
        
        <div style="display: flex; gap: 12px; margin-top: 30px;">
            <button type="submit" class="btn btn-primary" style="flex: 1; padding: 12px 24px; font-size: 16px; font-weight: 600;">
                💾 Save Settings
            </button>
        </div>
    </form>
</div>

<style>
.toggle-switch {
    position: relative;
    display: inline-block;
    width: 60px;
    height: 34px;
}

.toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.toggle-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .4s;
    border-radius: 34px;
}

.toggle-slider:before {
    position: absolute;
    content: "";
    height: 26px;
    width: 26px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    transition: .4s;
    border-radius: 50%;
}

.toggle-switch input:checked + .toggle-slider {
    background-color: #0ea5e9;
}

.toggle-switch input:checked + .toggle-slider:before {
    transform: translateX(26px);
}

input[type="range"] {
    -webkit-appearance: none;
    appearance: none;
}

input[type="range"]::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: #0ea5e9;
    cursor: pointer;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

input[type="range"]::-moz-range-thumb {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: #0ea5e9;
    cursor: pointer;
    border: none;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

#sizeSlider::-webkit-slider-thumb {
    background: #9370DB;
}

#sizeSlider::-moz-range-thumb {
    background: #9370DB;
}

#intensitySlider::-webkit-slider-thumb {
    background: #22c55e;
}

#intensitySlider::-moz-range-thumb {
    background: #22c55e;
}

.color-option:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
}
</style>

<script>
let selectedColors = <?php echo json_encode($colors); ?>;

function toggleColor(colorValue, element) {
    const index = selectedColors.indexOf(colorValue);
    if (index > -1) {
        selectedColors.splice(index, 1);
        element.style.borderColor = '#e5e7eb';
        element.style.boxShadow = 'none';
        element.querySelector('div > div:last-child').innerHTML = '<div style="font-weight: 600; color: #111827; margin-bottom: 4px;">' + element.querySelector('div > div:last-child > div').textContent + '</div>';
    } else {
        selectedColors.push(colorValue);
        element.style.borderColor = colorValue;
        element.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
        element.querySelector('div > div:last-child').innerHTML = '<div style="font-weight: 600; color: #111827; margin-bottom: 4px;">' + element.querySelector('div > div:last-child > div').textContent + '</div><div style="font-size: 12px; color: #22c55e; font-weight: 600;">✓ Selected</div>';
    }
    
    // Update hidden input
    document.getElementById('colorsInput').value = JSON.stringify(selectedColors);
    
    // Update selected colors display
    updateSelectedColorsDisplay();
    updatePreview();
}

function updateSelectedColorsDisplay() {
    const display = document.getElementById('selectedColorsDisplay');
    const count = document.getElementById('selectedCount');
    count.textContent = selectedColors.length;
    
    display.innerHTML = '';
    selectedColors.forEach(color => {
        const div = document.createElement('div');
        div.style.cssText = 'width: 30px; height: 30px; border-radius: 50%; background: ' + color + '; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.2);';
        display.appendChild(div);
    });
}

function updatePreview() {
    const enabled = document.getElementById('enableAnimations').checked;
    const statusDiv = document.getElementById('previewStatus');
    
    if (enabled) {
        statusDiv.style.borderLeftColor = '#22c55e';
        statusDiv.querySelector('p').innerHTML = '<span style="color: #22c55e;">●</span> Animations are currently <strong>active</strong> across all pages';
    } else {
        statusDiv.style.borderLeftColor = '#ef4444';
        statusDiv.querySelector('p').innerHTML = '<span style="color: #ef4444;">●</span> Animations are currently <strong>inactive</strong>';
    }
}

// Initialize color options on page load
document.addEventListener('DOMContentLoaded', function() {
    updateSelectedColorsDisplay();
    updatePreview();
});

// Custom form submit handler to reload animations after save
function handleFestiveFormSubmit(event, form) {
    event.preventDefault();
    
    const formData = new FormData(form);
    const data = {};
    for (let [key, value] of formData.entries()) {
        if (key === 'opacity_percent' || key === 'intensity_percent') {
            // Skip these, we use the hidden inputs
            continue;
        }
        data[key] = value;
    }
    
    // Handle enabled checkbox
    data.enabled = document.getElementById('enableAnimations').checked ? '1' : '0';
    
    // Get opacity and intensity from hidden inputs
    data.opacity = document.getElementById('opacityInput').value;
    data.intensity = document.getElementById('intensityInput').value;
    
    fetch(form.action, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            // Show success message
            const successMsg = document.createElement('div');
            successMsg.style.cssText = 'position: fixed; top: 20px; right: 20px; background: #22c55e; color: white; padding: 15px 20px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); z-index: 10000; font-weight: 600;';
            successMsg.textContent = '✓ Settings saved! Animations will start immediately.';
            document.body.appendChild(successMsg);
            setTimeout(() => successMsg.remove(), 3000);
            
            // Reload animations on all pages (including this one)
            if (window.reloadFestiveAnimations) {
                window.reloadFestiveAnimations();
            }
            
            // Also trigger reload in other tabs/windows
            if (typeof BroadcastChannel !== 'undefined') {
                const channel = new BroadcastChannel('festive-animations');
                channel.postMessage({ action: 'reload' });
            }
        } else {
            alert('Error: ' + (result.error || 'Failed to save settings'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error saving settings. Please try again.');
    });
    
    return false;
}

// Listen for broadcast messages to reload animations
if (typeof BroadcastChannel !== 'undefined') {
    const channel = new BroadcastChannel('festive-animations');
    channel.addEventListener('message', function(event) {
        if (event.data.action === 'reload' && window.reloadFestiveAnimations) {
            window.reloadFestiveAnimations();
        }
    });
}
</script>
