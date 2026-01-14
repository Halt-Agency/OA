# Merge Tags - Both Methods Available

## Understanding Divi Code Modules

**You're correct** - Divi Code Modules are standalone HTML/CSS/JS blocks. They don't automatically have access to:
- Theme PHP functions
- Theme JavaScript files (unless explicitly loaded)
- WordPress functions

However, `custom.js` IS being loaded by the theme (it's enqueued in `functions.php`), so the JavaScript should be available. But if it's not working, we have a standalone solution.

## Method 1: Direct JavaScript Variables (Recommended - Most Reliable)

The theme automatically injects ACF data into the page as `window.dtACFData`. You can access it directly in your code module.

### Example: Marquee Carousel Using Direct Variables

```html
<style>
.marquee-container {
    width: 100%;
    overflow: hidden;
    position: relative;
    padding: 40px 0;
}

.marquee-wrapper {
    display: flex;
    width: 200%;
}

.marquee-track {
    display: flex;
    gap: 30px;
    animation: marquee-scroll 30s linear infinite;
    width: 50%;
}

.marquee-track-duplicate {
    display: flex;
    gap: 30px;
    width: 50%;
}

.marquee-item {
    flex-shrink: 0;
}

.marquee-item img {
    max-width: 200px;
    height: auto;
    object-fit: contain;
    filter: grayscale(100%);
    opacity: 0.7;
    transition: all 0.3s ease;
}

.marquee-item img:hover {
    filter: grayscale(0%);
    opacity: 1;
    transform: scale(1.05);
}

@keyframes marquee-scroll {
    0% { transform: translateX(0); }
    100% { transform: translateX(-100%); }
}
</style>

<div class="marquee-container">
    <div class="marquee-wrapper">
        <div class="marquee-track" id="marquee-1"></div>
        <div class="marquee-track-duplicate" id="marquee-2"></div>
    </div>
</div>

<script>
(function() {
    // Wait for ACF data to be available
    function initMarquee() {
        // Check if ACF data is available
        if (typeof window.dtACFData === 'undefined') {
            // Wait a bit and try again
            setTimeout(initMarquee, 100);
            return;
        }
        
        // Get your repeater field (change 'test_repeater' to your field name)
        const repeater = window.dtACFData.test_repeater;
        const imageField = 'image'; // Change to your image sub-field name
        
        if (!repeater || !Array.isArray(repeater)) {
            console.warn('Repeater field not found or empty');
            return;
        }
        
        const track1 = document.getElementById('marquee-1');
        const track2 = document.getElementById('marquee-2');
        
        if (!track1 || !track2) return;
        
        // Build carousel
        repeater.forEach(function(row) {
            if (row[imageField]) {
                const image = row[imageField];
                const imgUrl = (image.url) ? image.url : image;
                const imgAlt = (image.alt) ? image.alt : '';
                
                // First track
                const item1 = document.createElement('div');
                item1.className = 'marquee-item';
                item1.innerHTML = '<img src="' + imgUrl + '" alt="' + imgAlt + '">';
                track1.appendChild(item1);
                
                // Duplicate for second track
                const item2 = item1.cloneNode(true);
                track2.appendChild(item2);
            }
        });
    }
    
    // Start when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMarquee);
    } else {
        initMarquee();
    }
})();
</script>
```

**No files required** - Everything is self-contained in the code module!

## Method 2: Merge Tags (Automatic Processing)

If the JavaScript processor is working, you can use merge tags and they'll be automatically replaced.

### Example: Using Merge Tags

```html
<style>
.marquee-container {
    width: 100%;
    overflow: hidden;
    position: relative;
    padding: 40px 0;
}

.marquee-wrapper {
    display: flex;
    width: 200%;
}

.marquee-track {
    display: flex;
    gap: 30px;
    animation: marquee-scroll 30s linear infinite;
    width: 50%;
}

.marquee-track-duplicate {
    display: flex;
    gap: 30px;
    width: 50%;
}

.marquee-item {
    flex-shrink: 0;
}

.marquee-item img {
    max-width: 200px;
    height: auto;
    object-fit: contain;
}

@keyframes marquee-scroll {
    0% { transform: translateX(0); }
    100% { transform: translateX(-100%); }
}
</style>

<div class="marquee-container">
    <div class="marquee-wrapper">
        <div class="marquee-track">
            <!-- Merge tags will be automatically replaced -->
            <div class="marquee-item">
                <img src="{acf:test_repeater:0:image}" alt="{acf:test_repeater:0:image:alt}">
            </div>
            <div class="marquee-item">
                <img src="{acf:test_repeater:1:image}" alt="{acf:test_repeater:1:image:alt}">
            </div>
            <div class="marquee-item">
                <img src="{acf:test_repeater:2:image}" alt="{acf:test_repeater:2:image:alt}">
            </div>
            <!-- Add more rows as needed -->
        </div>
        <div class="marquee-track-duplicate">
            <!-- Duplicate for seamless loop -->
            <div class="marquee-item">
                <img src="{acf:test_repeater:0:image}" alt="{acf:test_repeater:0:image:alt}">
            </div>
            <div class="marquee-item">
                <img src="{acf:test_repeater:1:image}" alt="{acf:test_repeater:1:image:alt}">
            </div>
            <div class="marquee-item">
                <img src="{acf:test_repeater:2:image}" alt="{acf:test_repeater:2:image:alt}">
            </div>
        </div>
    </div>
</div>
```

## Which Method to Use?

### Use Method 1 (Direct Variables) if:
- ✅ You want full control
- ✅ You want to loop through all repeater rows automatically
- ✅ You want the most reliable solution
- ✅ Merge tags aren't working

### Use Method 2 (Merge Tags) if:
- ✅ You want simpler HTML
- ✅ You only have a few items
- ✅ The automatic processing is working

## Important Notes

1. **No files need to be included** - Code modules are standalone
2. **`window.dtACFData` is automatically available** - The theme injects it into every page
3. **`custom.js` is automatically loaded** - But you don't need to reference it in code modules
4. **Everything is self-contained** - Each code module has its own HTML/CSS/JS

## Testing

1. **Test Method 1**: Copy the "Direct JavaScript Variables" example above
2. **Test Method 2**: Copy the "Merge Tags" example above
3. **Check browser console**: Open DevTools (F12) and check for:
   - `window.dtACFData` - Should show your ACF data
   - Any JavaScript errors

## Debugging

If Method 1 doesn't work, check in browser console:
```javascript
// Check if data is available
console.log(window.dtACFData);

// Check your repeater
console.log(window.dtACFData.test_repeater);
```

If `window.dtACFData` is undefined, the data injection might not be working. Let me know and we can fix it!
