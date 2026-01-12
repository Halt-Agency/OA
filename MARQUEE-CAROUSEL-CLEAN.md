# Marquee Carousel - Clean Production Version (Method 1)

Copy this code into your Divi Code Module:

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
    width: fit-content;
    animation: marquee-scroll 30s linear infinite;
    will-change: transform;
    backface-visibility: hidden;
    transform: translate3d(0, 0, 0);
  }

  .marquee-wrapper:hover {
    animation-play-state: paused;
  }

  .marquee-track {
    display: flex;
    gap: 30px;
    flex-shrink: 0;
  }

  .marquee-track-duplicate {
    display: flex;
    gap: 30px;
    flex-shrink: 0;
  }

  .marquee-item {
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    backface-visibility: hidden;
  }

  .marquee-item img {
    max-width: 200px;
    height: auto;
    object-fit: contain;
    filter: grayscale(100%);
    opacity: 0.7;
    transition: all 0.3s ease;
    display: block;
    backface-visibility: hidden;
  }

  .marquee-item img:hover {
    filter: grayscale(0%);
    opacity: 1;
    transform: scale(1.05);
  }

  @keyframes marquee-scroll {
    0% {
      transform: translate3d(0, 0, 0);
    }
    100% {
      transform: translate3d(-50%, 0, 0);
    }
  }

  @media (max-width: 768px) {
    .marquee-item img {
      max-width: 150px;
    }

    .marquee-track,
    .marquee-track-duplicate {
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
  (function () {
    "use strict";

    // Wait for ACF data to be available
    function initMarquee() {
      if (typeof window.dtACFData === "undefined") {
        // Retry after a short delay
        setTimeout(initMarquee, 100);
        return;
      }

      const repeater = window.dtACFData.test_repeater;
      const imageField = "image";
      const track1 = document.getElementById("marquee-track-1");
      const track2 = document.getElementById("marquee-track-2");

      if (!repeater || !Array.isArray(repeater) || repeater.length === 0) {
        console.warn("Marquee Carousel: No repeater data found");
        return;
      }

      if (!track1 || !track2) {
        console.warn("Marquee Carousel: Track elements not found");
        return;
      }

      // Clear any existing content
      track1.innerHTML = "";
      track2.innerHTML = "";

      // Process each repeater row
      repeater.forEach(function (row) {
        if (row[imageField]) {
          const image = row[imageField];
          let imgUrl = "";
          let imgAlt = "";

          // Handle image object format
          if (typeof image === "object" && image.url) {
            imgUrl = image.url;
            imgAlt = image.alt || "";
          }
          // Handle string format
          else if (typeof image === "string") {
            imgUrl = image;
          }

          if (imgUrl) {
            // Create image item for track 1
            const item1 = document.createElement("div");
            item1.className = "marquee-item";
            const img1 = document.createElement("img");
            img1.src = imgUrl;
            img1.alt = imgAlt;
            item1.appendChild(img1);
            track1.appendChild(item1);

            // Duplicate for track 2 (seamless loop)
            const item2 = item1.cloneNode(true);
            track2.appendChild(item2);
          }
        }
      });

      // Calculate the exact width needed for seamless animation
      const track1Width = track1.offsetWidth;
      const wrapper = track1.parentElement;
      if (wrapper && track1Width > 0) {
        // Set wrapper width to exactly double the first track width
        wrapper.style.width = track1Width * 2 + "px";
      }
    }

    // Initialize when DOM is ready
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", initMarquee);
    } else {
      initMarquee();
    }
  })();
</script>
```

## Customization Options

### Change Animation Speed

Change the `30s` in the animation to make it faster or slower:

```css
animation: marquee-scroll 20s linear infinite; /* Faster */
animation: marquee-scroll 40s linear infinite; /* Slower */
```

### Change Image Size

Modify the `max-width` value:

```css
.marquee-item img {
  max-width: 250px; /* Larger images */
}
```

### Change Gap Between Images

Modify the `gap` value:

```css
.marquee-track,
.marquee-track-duplicate {
  gap: 50px; /* More space between images */
}
```

### Change Repeater Field Name

If your repeater field has a different name, change it in the JavaScript:

```javascript
const repeater = window.dtACFData.your_repeater_name;
```

### Change Image Sub-field Name

If your image field has a different name, change it in the JavaScript:

```javascript
const imageField = "your_image_field_name";
```

## How It Works

1. The carousel uses `window.dtACFData` which is automatically injected by your theme's `functions.php`
2. It reads the `test_repeater` field from the ACF data
3. For each row in the repeater, it extracts the image URL and creates image elements
4. Images are added to both tracks for seamless infinite scrolling
5. The wrapper animates continuously, creating the marquee effect
