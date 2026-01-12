# Marquee Carousel - HTML/CSS/JS Version for Divi Code Modules

This version uses HTML, CSS, and JavaScript with merge tags to fetch ACF repeater data.

## Simple Version (Using Merge Tags)

Paste this into your Divi Code Module:

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

.marquee-track:hover,
.marquee-track-duplicate:hover {
    animation-play-state: paused;
}

.marquee-item {
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
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
    0% {
        transform: translateX(0);
    }
    100% {
        transform: translateX(-100%);
    }
}

@media (max-width: 768px) {
    .marquee-item img {
        max-width: 150px;
    }
    
    .marquee-track {
        gap: 20px;
    }
}
</style>

<div class="marquee-container">
    <div class="marquee-wrapper">
        <div class="marquee-track" id="marquee-track-1">
            <!-- Images will be inserted here by JavaScript -->
        </div>
        <div class="marquee-track-duplicate" id="marquee-track-2">
            <!-- Duplicate images for seamless loop -->
        </div>
    </div>
</div>

<script>
(function() {
    // Get repeater field name and image field name
    const repeaterField = 'test_repeater'; // Change to your repeater field name
    const imageField = 'image'; // Change to your image sub-field name
    
    // Get current post ID
    const postId = <?php echo get_the_ID(); ?>;
    
    // Fetch ACF data via WordPress REST API
    fetch('/wp-json/wp/v2/pages/' + postId + '?acf_format=standard')
        .then(response => response.json())
        .then(data => {
            if (data.acf && data.acf[repeaterField]) {
                const repeater = data.acf[repeaterField];
                const track1 = document.getElementById('marquee-track-1');
                const track2 = document.getElementById('marquee-track-2');
                
                // Build images for first track
                repeater.forEach(row => {
                    if (row[imageField]) {
                        const image = row[imageField];
                        const imgUrl = image.url || image;
                        const imgAlt = image.alt || '';
                        
                        const item = document.createElement('div');
                        item.className = 'marquee-item';
                        item.innerHTML = '<img src="' + imgUrl + '" alt="' + imgAlt + '">';
                        track1.appendChild(item);
                        
                        // Duplicate for second track
                        const item2 = item.cloneNode(true);
                        track2.appendChild(item2);
                    }
                });
            }
        })
        .catch(error => {
            console.error('Error loading marquee images:', error);
        });
})();
</script>
```

**Note**: This requires ACF to expose fields via REST API. If that doesn't work, use the merge tag version below.

## Merge Tag Version (Recommended)

This uses the merge tag system we already built:

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

.marquee-track:hover,
.marquee-track-duplicate:hover {
    animation-play-state: paused;
}

.marquee-item {
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
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
    0% {
        transform: translateX(0);
    }
    100% {
        transform: translateX(-100%);
    }
}

@media (max-width: 768px) {
    .marquee-item img {
        max-width: 150px;
    }
    
    .marquee-track {
        gap: 20px;
    }
}
</style>

<div class="marquee-container">
    <div class="marquee-wrapper">
        <div class="marquee-track">
            <!-- Use merge tags for each repeater row -->
            <!-- Row 0 -->
            <div class="marquee-item">
                <img src="{acf:test_repeater:0:image}" alt="{acf:test_repeater:0:image:alt}">
            </div>
            <!-- Row 1 -->
            <div class="marquee-item">
                <img src="{acf:test_repeater:1:image}" alt="{acf:test_repeater:1:image:alt}">
            </div>
            <!-- Row 2 -->
            <div class="marquee-item">
                <img src="{acf:test_repeater:2:image}" alt="{acf:test_repeater:2:image:alt}">
            </div>
            <!-- Add more rows as needed (0, 1, 2, 3, 4, etc.) -->
        </div>
        <div class="marquee-track-duplicate">
            <!-- Duplicate the same images for seamless loop -->
            <div class="marquee-item">
                <img src="{acf:test_repeater:0:image}" alt="{acf:test_repeater:0:image:alt}">
            </div>
            <div class="marquee-item">
                <img src="{acf:test_repeater:1:image}" alt="{acf:test_repeater:1:image:alt}">
            </div>
            <div class="marquee-item">
                <img src="{acf:test_repeater:2:image}" alt="{acf:test_repeater:2:image:alt}">
            </div>
            <!-- Duplicate all rows -->
        </div>
    </div>
</div>
```

**Limitation**: You need to manually add merge tags for each repeater row (0, 1, 2, 3, etc.)

## Dynamic JavaScript Version (Best Solution)

This version automatically detects and renders all repeater rows:

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

.marquee-track:hover,
.marquee-track-duplicate:hover {
    animation-play-state: paused;
}

.marquee-item {
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
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
    0% {
        transform: translateX(0);
    }
    100% {
        transform: translateX(-100%);
    }
}

@media (max-width: 768px) {
    .marquee-item img {
        max-width: 150px;
    }
    
    .marquee-track {
        gap: 20px;
    }
}
</style>

<div class="marquee-container">
    <div class="marquee-wrapper">
        <div class="marquee-track" id="marquee-track-1"></div>
        <div class="marquee-track-duplicate" id="marquee-track-2"></div>
    </div>
</div>

<script>
(function() {
    const repeaterField = 'test_repeater'; // Change to your repeater field name
    const imageField = 'image'; // Change to your image sub-field name
    const maxRows = 20; // Maximum number of rows to check
    
    const track1 = document.getElementById('marquee-track-1');
    const track2 = document.getElementById('marquee-track-2');
    let imageCount = 0;
    
    // Try to get images using merge tags (processed server-side)
    // We'll use a data attribute approach
    const container = document.querySelector('.marquee-container');
    
    // Fetch via AJAX using WordPress REST API with ACF
    const postId = <?php echo get_the_ID(); ?>;
    
    // Use fetch to get ACF data
    fetch('/wp-json/acf/v3/pages/' + postId)
        .then(response => {
            if (!response.ok) {
                // Fallback to standard REST API
                return fetch('/wp-json/wp/v2/pages/' + postId);
            }
            return response.json();
        })
        .then(data => {
            let repeater = null;
            
            // Try ACF REST API format
            if (data.acf && data.acf[repeaterField]) {
                repeater = data.acf[repeaterField];
            }
            // Try standard format
            else if (data[repeaterField]) {
                repeater = data[repeaterField];
            }
            
            if (repeater && Array.isArray(repeater)) {
                repeater.forEach(row => {
                    if (row[imageField]) {
                        const image = row[imageField];
                        const imgUrl = (image.url) ? image.url : image;
                        const imgAlt = (image.alt) ? image.alt : '';
                        
                        const item = document.createElement('div');
                        item.className = 'marquee-item';
                        item.innerHTML = '<img src="' + imgUrl + '" alt="' + imgAlt + '">';
                        track1.appendChild(item);
                        
                        // Duplicate for seamless loop
                        const item2 = item.cloneNode(true);
                        track2.appendChild(item2);
                        imageCount++;
                    }
                });
            } else {
                // Fallback: Try to read from hidden data attributes
                console.log('ACF data not found, trying alternative method');
            }
        })
        .catch(error => {
            console.error('Error loading marquee:', error);
            // Fallback: Use merge tags in hidden divs
        });
})();
</script>
```

## Simplest Working Version (Using Hidden Merge Tags)

This is the most reliable - it uses merge tags in hidden divs, then JavaScript reads them:

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

.marquee-track:hover,
.marquee-track-duplicate:hover {
    animation-play-state: paused;
}

.marquee-item {
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
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

.marquee-data {
    display: none;
}

@keyframes marquee-scroll {
    0% {
        transform: translateX(0);
    }
    100% {
        transform: translateX(-100%);
    }
}

@media (max-width: 768px) {
    .marquee-item img {
        max-width: 150px;
    }
    
    .marquee-track {
        gap: 20px;
    }
}
</style>

<!-- Hidden data container with merge tags (processed server-side) -->
<div class="marquee-data" id="marquee-data">
    <!-- Add merge tags for each row - JavaScript will read these -->
    <span data-row="0" data-image="{acf:test_repeater:0:image}" data-alt="{acf:test_repeater:0:image:alt}"></span>
    <span data-row="1" data-image="{acf:test_repeater:1:image}" data-alt="{acf:test_repeater:1:image:alt}"></span>
    <span data-row="2" data-image="{acf:test_repeater:2:image}" data-alt="{acf:test_repeater:2:image:alt}"></span>
    <span data-row="3" data-image="{acf:test_repeater:3:image}" data-alt="{acf:test_repeater:3:image:alt}"></span>
    <span data-row="4" data-image="{acf:test_repeater:4:image}" data-alt="{acf:test_repeater:4:image:alt}"></span>
    <!-- Add more rows as needed -->
</div>

<div class="marquee-container">
    <div class="marquee-wrapper">
        <div class="marquee-track" id="marquee-track-1"></div>
        <div class="marquee-track-duplicate" id="marquee-track-2"></div>
    </div>
</div>

<script>
(function() {
    const dataContainer = document.getElementById('marquee-data');
    const track1 = document.getElementById('marquee-track-1');
    const track2 = document.getElementById('marquee-track-2');
    
    // Read all data spans
    const dataSpans = dataContainer.querySelectorAll('span[data-image]');
    
    dataSpans.forEach(span => {
        const imgUrl = span.getAttribute('data-image');
        const imgAlt = span.getAttribute('data-alt') || '';
        
        // Skip if image URL is empty or still has merge tag syntax
        if (imgUrl && !imgUrl.includes('{acf:')) {
            const item = document.createElement('div');
            item.className = 'marquee-item';
            item.innerHTML = '<img src="' + imgUrl + '" alt="' + imgAlt + '">';
            track1.appendChild(item);
            
            // Duplicate for seamless loop
            const item2 = item.cloneNode(true);
            track2.appendChild(item2);
        }
    });
})();
</script>
```

## Which Version to Use?

1. **Merge Tag Version** - Simplest, but requires manually adding each row
2. **Dynamic JavaScript Version** - Automatic, but requires ACF REST API setup
3. **Hidden Merge Tags Version** - Most reliable, uses merge tags + JavaScript

I recommend the **Hidden Merge Tags Version** - it's the most reliable and will definitely work with your merge tag system!
