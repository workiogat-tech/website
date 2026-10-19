<?php
/**
 * Give Back Tab - Complete Implementation
 */
$giveback = $db->fetchOne("SELECT * FROM giveback_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?? [];
$paymentDetails = [];
if (!empty($giveback['payment_details'])) {
    $paymentDetails = is_string($giveback['payment_details']) ? json_decode($giveback['payment_details'], true) : $giveback['payment_details'];
    if (!is_array($paymentDetails)) $paymentDetails = [];
}

$needs = [];
if (!empty($giveback['needs'])) {
    $needs = is_string($giveback['needs']) ? json_decode($giveback['needs'], true) : $giveback['needs'];
    if (!is_array($needs)) $needs = [];
}
?>
<div class="content-card">
    <h2>💰 Give Back</h2>
    <p style="margin-bottom: 30px; color: #666;">Manage give back program content, needs, contact information, and payment details.</p>
    
    <form method="POST" action="api/content/giveback.php" id="givebackForm" onsubmit="return handleGivebackFormSubmit(event, this);">
        
        <!-- Basic Content Section -->
        <div style="background: white; padding: 25px; border-radius: 12px; border: 1px solid #e5e7eb; margin-bottom: 30px;">
            <h3 style="font-size: 20px; font-weight: 600; color: #333; margin-bottom: 20px;">Basic Content</h3>
            
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label">Page Title</label>
                <input type="text" name="title" class="form-input" 
                       value="<?php echo htmlspecialchars($giveback['title'] ?? ''); ?>" 
                       placeholder="Enter page title">
            </div>
            
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label">Page Subtitle</label>
                <textarea name="subtitle" class="form-input" rows="3" 
                          placeholder="Enter page subtitle"><?php echo htmlspecialchars($giveback['subtitle'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-group">
                <label class="form-label">Hero Background Image</label>
                <div style="display: flex; align-items: center; gap: 15px;">
                    <?php 
                    $heroImageUrl = normalizeImageUrl($giveback['hero_image'] ?? '');
                    if (!empty($heroImageUrl)): ?>
                        <img src="<?php echo htmlspecialchars($heroImageUrl); ?>" 
                             alt="Hero background preview"
                             style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px; border: 2px solid #10b981;"
                             onerror="this.onerror=null; this.style.display='none';">
                    <?php endif; ?>
                    <div style="flex: 1;">
                        <input type="text" name="hero_image" 
                               id="hero_image" 
                               class="form-input" 
                               value="<?php echo htmlspecialchars($giveback['hero_image'] ?? ''); ?>" 
                               placeholder="Select image from media library" readonly>
                    </div>
                    <button type="button" onclick="openMediaLibrary('hero_image')" 
                            class="btn btn-primary" style="background: linear-gradient(135deg, #10b981, #059669);">
                        🖼️ Select Image
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Our Needs Section -->
        <div style="background: linear-gradient(135deg, #ecfdf5, #d1fae5); padding: 25px; border-radius: 12px; border: 2px solid #10b981; margin-bottom: 30px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="font-size: 20px; font-weight: 600; color: #333; margin: 0;">Our Needs</h3>
                <button type="button" onclick="addNeed()" class="btn btn-primary" style="background: linear-gradient(135deg, #10b981, #059669);">
                    ➕ Add Need
                </button>
            </div>
            
            <div id="needsContainer">
                <?php if (!empty($needs)): ?>
                    <?php foreach ($needs as $index => $need): 
                        $need = array_merge(['title' => '', 'image' => '', 'description' => ''], $need);
                    ?>
                        <div class="need-item" data-index="<?php echo $index; ?>" style="background: white; padding: 20px; border-radius: 8px; margin-bottom: 15px; border: 1px solid #d1fae5;">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
                                <h4 style="font-size: 16px; font-weight: 600; color: #333; margin: 0;">Need <?php echo $index + 1; ?></h4>
                                <button type="button" onclick="removeNeed(<?php echo $index; ?>)" class="btn btn-danger" style="padding: 5px 15px; font-size: 12px;">
                                    🗑️ Remove
                                </button>
                            </div>
                            
                            <div class="form-group" style="margin-bottom: 15px;">
                                <label class="form-label">Title</label>
                                <input type="text" name="needs[<?php echo $index; ?>][title]" class="form-input" 
                                       value="<?php echo htmlspecialchars($need['title']); ?>" 
                                       placeholder="e.g., Sports Equipment">
                            </div>
                            
                            <div class="form-group" style="margin-bottom: 15px;">
                                <label class="form-label">Image</label>
                                <div style="display: flex; align-items: center; gap: 15px;">
                                    <?php 
                                    $needImageUrl = normalizeImageUrl($need['image'] ?? '');
                                    if (!empty($needImageUrl)): ?>
                                        <img src="<?php echo htmlspecialchars($needImageUrl); ?>" 
                                             alt="Need image preview"
                                             style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px; border: 2px solid #10b981;"
                                             onerror="this.onerror=null; this.style.display='none';">
                                    <?php endif; ?>
                                    <div style="flex: 1;">
                                        <input type="text" name="needs[<?php echo $index; ?>][image]" 
                                               id="need_image_<?php echo $index; ?>" 
                                               class="form-input" 
                                               value="<?php echo htmlspecialchars($need['image']); ?>" 
                                               placeholder="Select image from media library" readonly>
                                    </div>
                                    <button type="button" onclick="openMediaLibrary('need_image_<?php echo $index; ?>')" 
                                            class="btn btn-primary" style="background: linear-gradient(135deg, #10b981, #059669);">
                                        🖼️ Select Image
                                    </button>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Description</label>
                                <textarea name="needs[<?php echo $index; ?>][description]" class="form-input" rows="4" 
                                          placeholder="Describe the need..."><?php echo htmlspecialchars($need['description']); ?></textarea>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <div id="emptyNeedsMessage" style="<?php echo !empty($needs) ? 'display: none;' : ''; ?> text-align: center; padding: 40px; color: #999; background: white; border-radius: 8px; border: 2px dashed #d1fae5;">
                <p style="margin: 0;">No needs added yet. Click "+ Add Need" to get started.</p>
            </div>
        </div>
        
        <!-- Contact Information Section -->
        <div style="background: linear-gradient(135deg, #eff6ff, #dbeafe); padding: 25px; border-radius: 12px; border: 2px solid #3b82f6; margin-bottom: 30px;">
            <h3 style="font-size: 20px; font-weight: 600; color: #333; margin-bottom: 20px;">📞 Contact Information</h3>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">
                <div class="form-group">
                    <label class="form-label">Phone</label>
                    <input type="text" name="contact_phone" class="form-input" 
                           value="<?php echo htmlspecialchars($giveback['contact_phone'] ?? ''); ?>" 
                           placeholder="+256 705666621">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="contact_email" class="form-input" 
                           value="<?php echo htmlspecialchars($giveback['contact_email'] ?? ''); ?>" 
                           placeholder="info@sjs.edu">
                </div>
            </div>
        </div>
        
        <!-- Payment Details Section -->
        <div style="background: linear-gradient(135deg, #fef3c7, #fde68a); padding: 25px; border-radius: 12px; border: 2px solid #f59e0b; margin-bottom: 30px;">
            <h3 style="font-size: 20px; font-weight: 600; color: #333; margin-bottom: 15px;">💳 Payment Details</h3>
            <p style="color: #666; font-size: 14px; margin-bottom: 20px;">
                Configure payment methods for donations. These details will be shown when users click "Donate" buttons.
            </p>
            
            <!-- Bank Transfer -->
            <div style="background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #f59e0b;">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px;">
                    <div style="width: 32px; height: 32px; background: linear-gradient(135deg, #10b981, #059669); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; font-size: 18px; font-weight: bold;">🏦</div>
                    <h4 style="font-size: 16px; font-weight: 600; color: #333; margin: 0;">Bank Transfer</h4>
                </div>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                    <div class="form-group">
                        <label class="form-label">Bank Name</label>
                        <input type="text" name="payment_details[bank_name]" class="form-input" 
                               value="<?php echo htmlspecialchars($paymentDetails['bank_name'] ?? $paymentDetails['bankName'] ?? ''); ?>" 
                               placeholder="e.g., Bank of Uganda">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Account Name</label>
                        <input type="text" name="payment_details[account_name]" class="form-input" 
                               value="<?php echo htmlspecialchars($paymentDetails['account_name'] ?? $paymentDetails['accountName'] ?? ''); ?>" 
                               placeholder="e.g., Sanyu Primary School">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Account Number</label>
                        <input type="text" name="payment_details[account_number]" class="form-input" 
                               value="<?php echo htmlspecialchars($paymentDetails['account_number'] ?? $paymentDetails['accountNumber'] ?? ''); ?>" 
                               placeholder="e.g., 1234567890">
                    </div>
                </div>
            </div>
            
            <!-- Mobile Money -->
            <div style="background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #f59e0b;">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px;">
                    <div style="width: 32px; height: 32px; background: linear-gradient(135deg, #3b82f6, #2563eb); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; font-size: 18px; font-weight: bold;">📱</div>
                    <h4 style="font-size: 16px; font-weight: 600; color: #333; margin: 0;">Mobile Money</h4>
                </div>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                    <div class="form-group">
                        <label class="form-label">Account Name</label>
                        <input type="text" name="payment_details[mobile_money_name]" class="form-input" 
                               value="<?php echo htmlspecialchars($paymentDetails['mobile_money_name'] ?? $paymentDetails['mobileMoneyName'] ?? ''); ?>" 
                               placeholder="e.g., sjs">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="payment_details[mobile_money_phone]" class="form-input" 
                               value="<?php echo htmlspecialchars($paymentDetails['mobile_money_phone'] ?? $paymentDetails['mobileMoneyPhone'] ?? ''); ?>" 
                               placeholder="e.g., 0703574112">
                    </div>
                </div>
            </div>
            
            <!-- Payment Instructions -->
            <div style="background: white; padding: 20px; border-radius: 8px; border: 1px solid #f59e0b;">
                <h4 style="font-size: 16px; font-weight: 600; color: #333; margin-bottom: 10px;">Payment Instructions</h4>
                <p style="color: #666; font-size: 13px; margin-bottom: 10px;">Optional: Add any special instructions for donors</p>
                <textarea name="payment_details[instructions]" class="form-input" rows="3" 
                          placeholder="e.g., Please include your name and donation purpose in the payment reference. After payment, contact us to confirm your donation."><?php echo htmlspecialchars($paymentDetails['instructions'] ?? ''); ?></textarea>
            </div>
        </div>
        
        <div style="display: flex; gap: 10px; margin-top: 20px;">
            <button type="submit" class="btn btn-primary" style="flex: 1; background: linear-gradient(135deg, #10b981, #059669);">
                💾 Save Give Back Content
            </button>
            <button type="button" onclick="window.open('give-back.php', '_blank')" class="btn" style="background: linear-gradient(135deg, #9333ea, #7c3aed); color: white;">
                👁️ Preview
            </button>
        </div>
    </form>
</div>

<script>
// Needs Management Functions
let needIndex = <?php echo count($needs); ?>;

function addNeed() {
    const container = document.getElementById('needsContainer');
    const emptyMessage = document.getElementById('emptyNeedsMessage');
    
    const needHtml = `
        <div class="need-item" data-index="${needIndex}" style="background: white; padding: 20px; border-radius: 8px; margin-bottom: 15px; border: 1px solid #d1fae5;">
            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
                <h4 style="font-size: 16px; font-weight: 600; color: #333; margin: 0;">Need ${needIndex + 1}</h4>
                <button type="button" onclick="removeNeed(${needIndex})" class="btn btn-danger" style="padding: 5px 15px; font-size: 12px;">
                    🗑️ Remove
                </button>
            </div>
            
            <div class="form-group" style="margin-bottom: 15px;">
                <label class="form-label">Title</label>
                <input type="text" name="needs[${needIndex}][title]" class="form-input" 
                       placeholder="e.g., Sports Equipment">
            </div>
            
            <div class="form-group" style="margin-bottom: 15px;">
                <label class="form-label">Image</label>
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div style="flex: 1;">
                        <input type="text" name="needs[${needIndex}][image]" 
                               id="need_image_${needIndex}" 
                               class="form-input" 
                               placeholder="Select image from media library" readonly>
                    </div>
                    <button type="button" onclick="openMediaLibrary('need_image_${needIndex}')" 
                            class="btn btn-primary" style="background: linear-gradient(135deg, #10b981, #059669);">
                        🖼️ Select Image
                    </button>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="needs[${needIndex}][description]" class="form-input" rows="4" 
                          placeholder="Describe the need..."></textarea>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', needHtml);
    needIndex++;
    
    if (emptyMessage) {
        emptyMessage.style.display = 'none';
    }
    
    // Scroll to the new need
    const newNeed = container.lastElementChild;
    if (newNeed) {
        newNeed.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
}

function removeNeed(index) {
    const needItem = document.querySelector(`.need-item[data-index="${index}"]`);
    if (needItem) {
        needItem.remove();
        
        // Check if any needs remain
        const container = document.getElementById('needsContainer');
        const emptyMessage = document.getElementById('emptyNeedsMessage');
        const remainingNeeds = container.querySelectorAll('.need-item');
        
        if (remainingNeeds.length === 0 && emptyMessage) {
            emptyMessage.style.display = 'block';
        }
        
        // Reindex remaining needs
        reindexNeeds();
    }
}

function reindexNeeds() {
    const container = document.getElementById('needsContainer');
    const needs = container.querySelectorAll('.need-item');
    needs.forEach((need, newIndex) => {
        need.setAttribute('data-index', newIndex);
        const title = need.querySelector('h4');
        if (title) {
            title.textContent = `Need ${newIndex + 1}`;
        }
        
        // Update all input names
        const inputs = need.querySelectorAll('input, textarea');
        inputs.forEach(input => {
            if (input.name) {
                input.name = input.name.replace(/needs\[\d+\]/, `needs[${newIndex}]`);
            }
            if (input.id) {
                input.id = input.id.replace(/need_image_\d+/, `need_image_${newIndex}`);
            }
        });
        
        // Update remove button and image button
        const removeBtn = need.querySelector('button[onclick*="removeNeed"]');
        if (removeBtn) {
            removeBtn.setAttribute('onclick', `removeNeed(${newIndex})`);
        }
        const imageBtn = need.querySelector('button[onclick*="openMediaLibrary"]');
        if (imageBtn) {
            imageBtn.setAttribute('onclick', `openMediaLibrary('need_image_${newIndex}')`);
        }
    });
    
    needIndex = needs.length;
}

// Handle form submission - convert needs array properly
function handleGivebackFormSubmit(event, form) {
    event.preventDefault();
    
    // Collect needs data
    const needs = [];
    const needItems = document.querySelectorAll('.need-item');
    
    needItems.forEach((item) => {
        const title = item.querySelector('input[name*="[title]"]')?.value?.trim();
        const image = item.querySelector('input[name*="[image]"]')?.value?.trim();
        const description = item.querySelector('textarea[name*="[description]"]')?.value?.trim();
        
        if (title || image || description) {
            needs.push({
                title: title || '',
                image: image || '',
                description: description || ''
            });
        }
    });
    
    // Collect payment details
    const paymentDetails = {
        bank_name: form.querySelector('input[name="payment_details[bank_name]"]')?.value || '',
        account_name: form.querySelector('input[name="payment_details[account_name]"]')?.value || '',
        account_number: form.querySelector('input[name="payment_details[account_number]"]')?.value || '',
        mobile_money_name: form.querySelector('input[name="payment_details[mobile_money_name]"]')?.value || '',
        mobile_money_phone: form.querySelector('input[name="payment_details[mobile_money_phone]"]')?.value || '',
        instructions: form.querySelector('textarea[name="payment_details[instructions]"]')?.value || ''
    };
    
    // Prepare data
    const data = {
        title: form.querySelector('input[name="title"]')?.value || '',
        subtitle: (form.querySelector('textarea[name="subtitle"]') || form.querySelector('input[name="subtitle"]'))?.value || '',
        description: form.querySelector('textarea[name="description"]')?.value || '',
        hero_image: form.querySelector('input[name="hero_image"]')?.value || '',
        contact_phone: form.querySelector('input[name="contact_phone"]')?.value || '',
        contact_email: form.querySelector('input[name="contact_email"]')?.value || '',
        payment_details: paymentDetails,
        needs: needs
    };
    
    // Send AJAX request
    fetch(form.action, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(async response => {
        const text = await response.text();
        let result;
        try {
            result = JSON.parse(text);
        } catch (e) {
            console.error('Failed to parse response:', text);
            throw new Error('Server returned invalid response');
        }
        
        if (result.error) {
            showToast(result.error, 'error');
        } else {
            showToast(result.message || 'Giveback content saved successfully!', 'success');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Failed to save content. Please try again.', 'error');
    });
    
    return false;
}
</script>
