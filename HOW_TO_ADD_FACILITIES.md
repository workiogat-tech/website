# How to Add School Facilities

This guide explains how to add and manage school facilities that will be displayed on the website with images, descriptions, labels, and capacity information.

## Accessing the Facilities Admin Panel

1. Log in to the admin panel at `http://localhost/2026sjs/admin.php`
2. Click on **"Facilities"** in the left sidebar navigation
3. You'll see the Facilities management page

## Adding a New Facility

### Step 1: Click "Add Facility" Button
- Click the **"➕ Add Facility"** button at the top right of the facilities list

### Step 2: Fill in the Facility Information

The form includes the following fields:

#### **Name** (Required)
- Enter the facility name (e.g., "Clinic", "Washroom", "Library", "Computer Lab")
- This appears as the main title on the facility card
- Example: `Clinic`

#### **Image Label (Overlay)** (Optional)
- This is the label that appears as an overlay badge on the top-left of the facility image
- If left empty, the facility name will be used instead
- Examples: 
  - `Nurse Office` (for Clinic)
  - `Recreation` (for Washroom)
  - `Study Area` (for Library)

#### **Description** (Optional)
- Write a detailed description of the facility
- This appears below the title on the facility card
- Example: 
  ```
  The school clinic provides basic health care and first aid to ensure the wellbeing of all learners. Trained staff are always available to support children in case of illness or minor injuries.
  ```

#### **Capacity (Number of People)** (Optional)
- Enter the maximum number of people the facility can accommodate
- This will be displayed with a people icon (👥) at the bottom of the facility card
- Example: `20` (will display as "Capacity: 20 people")

#### **Image** (Optional but Recommended)
- Click the **"🖼️ Select"** button to open the Media Library
- Browse your uploaded images or upload a new one
- Click on the image you want to use
- The image URL will automatically fill in
- **Recommended image size:** 800x600px or larger for best quality
- The image will be displayed at the top of the facility card

### Step 3: Save the Facility
- Click the **"💾 Save Facility"** button
- A success message will appear
- The page will refresh and show your new facility in the list

## Editing a Facility

1. Find the facility in the facilities table
2. Click the **"Edit"** button in the Actions column
3. Modify any fields you want to change
4. Click **"💾 Update Facility"** to save changes

## Deleting a Facility

1. Find the facility in the facilities table
2. Click the **"Delete"** button in the Actions column
3. Confirm the deletion when prompted
- Note: This is a "soft delete" - the facility is hidden but not permanently removed from the database

## Example: Adding a Clinic Facility

Here's a complete example of how to add a clinic facility:

1. **Name:** `Clinic`
2. **Image Label:** `Nurse Office`
3. **Description:** `The school clinic provides basic health care and first aid to ensure the wellbeing of all learners. Trained staff are always available to support children in case of illness or minor injuries.`
4. **Capacity:** `20`
5. **Image:** Select an image of a school clinic/nurse office

## Example: Adding a Washroom Facility

1. **Name:** `Washroom`
2. **Image Label:** `Recreation`
3. **Description:** `Our washrooms are clean, safe, and well-maintained to ensure hygiene and comfort for all learners. We encourage students to practice good personal hygiene and respect shared facilities.`
4. **Capacity:** `12`
5. **Image:** Select an image of a clean, modern washroom

## How Facilities Appear on the Website

Facilities are displayed on the Facilities page (`facilities.php`) with:

- **Image at the top** - Full-width image showing the facility
- **Label overlay** - Small badge in the top-left corner of the image (shows the "Image Label" or facility name)
- **Title** - The facility name in bold, uppercase text
- **Description** - The detailed description text
- **Capacity** - Displayed with a people icon (👥) showing "Capacity: X people"

## Tips for Best Results

1. **Use High-Quality Images:** Upload clear, well-lit photos of your facilities
2. **Write Clear Descriptions:** Keep descriptions informative but concise (2-3 sentences)
3. **Set Appropriate Capacity:** Use realistic numbers based on the actual facility size
4. **Use Descriptive Labels:** The image label should quickly identify what the facility is (e.g., "Nurse Office" is clearer than just "Clinic")
5. **Consistent Naming:** Use consistent naming conventions (e.g., all caps for titles: "CLINIC", "WASHROOM")

## Troubleshooting

- **Image not showing?** Check that the image URL is correct and the image file exists
- **Capacity not displaying?** Make sure you entered a number in the capacity field
- **Label not showing?** If you didn't enter a label, the facility name will be used instead
- **Changes not saving?** Make sure you're logged in as an admin and click the save button

## Need Help?

If you encounter any issues:
1. Check the browser console for error messages
2. Verify you're logged in as an administrator
3. Ensure all required fields are filled
4. Check that image URLs are valid
