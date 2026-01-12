# Test Code Block - Both Methods with Debugging

Copy this entire code block into your Divi Code Module:

```html
<style>
.debug-container {
    background: #f5f5f5;
    padding: 20px;
    margin: 20px 0;
    border: 2px solid #333;
    font-family: monospace;
    font-size: 12px;
}

.debug-section {
    margin: 20px 0;
    padding: 15px;
    background: white;
    border-left: 4px solid #0073aa;
}

.debug-success {
    border-left-color: #46b450;
    background: #f0f8f0;
}

.debug-error {
    border-left-color: #dc3232;
    background: #fff0f0;
}

.debug-title {
    font-weight: bold;
    font-size: 14px;
    margin-bottom: 10px;
    color: #0073aa;
}

.debug-output {
    background: #1e1e1e;
    color: #d4d4d4;
    padding: 10px;
    border-radius: 4px;
    overflow-x: auto;
    max-height: 200px;
    overflow-y: auto;
}

.marquee-container {
    width: 100%;
    overflow: hidden;
    position: relative;
    padding: 40px 0;
    background: #f9f9f9;
    margin-top: 20px;
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

<div class="debug-container">
    <h2>Merge Tags Debug Test</h2>
    
    <div class="debug-section" id="debug-data-check">
        <div class="debug-title">Step 1: Checking if ACF Data is Available</div>
        <div class="debug-output" id="data-check-output">Checking...</div>
    </div>
    
    <div class="debug-section" id="debug-method1">
        <div class="debug-title">Step 2: Method 1 - Direct JavaScript Variables</div>
        <div class="debug-output" id="method1-output">Testing...</div>
        <div id="method1-result"></div>
    </div>
    
    <div class="debug-section" id="debug-method2">
        <div class="debug-title">Step 3: Method 2 - Merge Tags (Automatic Processing)</div>
        <div class="debug-output" id="method2-output">Testing...</div>
        <div id="method2-result"></div>
    </div>
</div>

<!-- Method 2 Test: Merge Tags -->
<div class="marquee-container" id="marquee-method2">
    <div class="marquee-wrapper">
        <div class="marquee-track">
            <div class="marquee-item">
                <img src="{acf:test_repeater:0:image}" alt="{acf:test_repeater:0:image:alt}">
            </div>
            <div class="marquee-item">
                <img src="{acf:test_repeater:1:image}" alt="{acf:test_repeater:1:image:alt}">
            </div>
        </div>
        <div class="marquee-track-duplicate">
            <div class="marquee-item">
                <img src="{acf:test_repeater:0:image}" alt="{acf:test_repeater:0:image:alt}">
            </div>
            <div class="marquee-item">
                <img src="{acf:test_repeater:1:image}" alt="{acf:test_repeater:1:image:alt}">
            </div>
        </div>
    </div>
</div>

<!-- Method 1 Test: Direct Variables -->
<div class="marquee-container" id="marquee-method1">
    <div class="marquee-wrapper">
        <div class="marquee-track" id="marquee-track-1"></div>
        <div class="marquee-track-duplicate" id="marquee-track-2"></div>
    </div>
</div>

<script>
(function() {
    'use strict';
    
    console.log('=== MERGE TAGS DEBUG TEST STARTED ===');
    
    // Debug output function
    function debugLog(elementId, message, isError) {
        const element = document.getElementById(elementId);
        if (element) {
            element.innerHTML += message + '<br>';
            if (isError) {
                element.parentElement.classList.add('debug-error');
            } else {
                element.parentElement.classList.add('debug-success');
            }
        }
        console.log(message);
    }
    
    // Step 1: Check if ACF data is available
    function checkACFData() {
        debugLog('data-check-output', 'Checking window.dtACFData...');
        
        if (typeof window.dtACFData !== 'undefined') {
            debugLog('data-check-output', '✅ window.dtACFData EXISTS');
            debugLog('data-check-output', 'Data structure: ' + JSON.stringify(window.dtACFData, null, 2));
            
            if (window.dtACFData.test_repeater) {
                debugLog('data-check-output', '✅ test_repeater field found');
                debugLog('data-check-output', 'Number of rows: ' + window.dtACFData.test_repeater.length);
            } else {
                debugLog('data-check-output', '❌ test_repeater field NOT found');
                debugLog('data-check-output', 'Available fields: ' + Object.keys(window.dtACFData).join(', '), true);
            }
        } else {
            debugLog('data-check-output', '❌ window.dtACFData is UNDEFINED', true);
            debugLog('data-check-output', 'This means ACF data injection is not working', true);
        }
        
        if (typeof window.dtPostId !== 'undefined') {
            debugLog('data-check-output', '✅ window.dtPostId: ' + window.dtPostId);
        } else {
            debugLog('data-check-output', '⚠️ window.dtPostId is undefined');
        }
    }
    
    // Step 2: Test Method 1 - Direct JavaScript Variables
    function testMethod1() {
        debugLog('method1-output', '=== METHOD 1 TEST START ===');
        
        if (typeof window.dtACFData === 'undefined') {
            debugLog('method1-output', '❌ Cannot test - window.dtACFData not available', true);
            return;
        }
        
        const repeater = window.dtACFData.test_repeater;
        const imageField = 'image';
        
        debugLog('method1-output', 'Looking for repeater: test_repeater');
        debugLog('method1-output', 'Repeater found: ' + (repeater ? 'YES' : 'NO'));
        
        if (!repeater || !Array.isArray(repeater)) {
            debugLog('method1-output', '❌ Repeater is not an array or is empty', true);
            debugLog('method1-output', 'Type: ' + typeof repeater, true);
            return;
        }
        
        debugLog('method1-output', '✅ Repeater has ' + repeater.length + ' rows');
        
        const track1 = document.getElementById('marquee-track-1');
        const track2 = document.getElementById('marquee-track-2');
        
        if (!track1 || !track2) {
            debugLog('method1-output', '❌ Could not find marquee track elements', true);
            return;
        }
        
        let imageCount = 0;
        
        repeater.forEach(function(row, index) {
            debugLog('method1-output', 'Processing row ' + index);
            
            if (row[imageField]) {
                const image = row[imageField];
                debugLog('method1-output', '  Row ' + index + ' has image field');
                
                let imgUrl = '';
                let imgAlt = '';
                
                if (typeof image === 'object' && image.url) {
                    imgUrl = image.url;
                    imgAlt = image.alt || '';
                    debugLog('method1-output', '  Image URL: ' + imgUrl);
                } else if (typeof image === 'string') {
                    imgUrl = image;
                    debugLog('method1-output', '  Image URL (string): ' + imgUrl);
                } else {
                    debugLog('method1-output', '  ⚠️ Image format not recognized: ' + typeof image, true);
                    return;
                }
                
                if (imgUrl) {
                    const item1 = document.createElement('div');
                    item1.className = 'marquee-item';
                    item1.innerHTML = '<img src="' + imgUrl + '" alt="' + imgAlt + '">';
                    track1.appendChild(item1);
                    
                    const item2 = item1.cloneNode(true);
                    track2.appendChild(item2);
                    
                    imageCount++;
                    debugLog('method1-output', '  ✅ Added image ' + imageCount);
                }
            } else {
                debugLog('method1-output', '  Row ' + index + ' has NO image field');
            }
        });
        
        if (imageCount > 0) {
            debugLog('method1-output', '✅ METHOD 1 SUCCESS: Added ' + imageCount + ' images');
            document.getElementById('method1-result').innerHTML = '<p style="color: green; font-weight: bold;">✅ Method 1 WORKED! ' + imageCount + ' images displayed.</p>';
        } else {
            debugLog('method1-output', '❌ METHOD 1 FAILED: No images were added', true);
            document.getElementById('method1-result').innerHTML = '<p style="color: red; font-weight: bold;">❌ Method 1 FAILED - No images found</p>';
        }
    }
    
    // Step 3: Test Method 2 - Merge Tags
    function testMethod2() {
        debugLog('method2-output', '=== METHOD 2 TEST START ===');
        
        const marqueeContainer = document.getElementById('marquee-method2');
        if (!marqueeContainer) {
            debugLog('method2-output', '❌ Could not find marquee container', true);
            return;
        }
        
        const htmlContent = marqueeContainer.innerHTML;
        debugLog('method2-output', 'Original HTML contains merge tags: ' + (htmlContent.includes('{acf:') ? 'YES' : 'NO'));
        
        // Check if merge tags were processed
        const mergeTagPattern = /\{acf:([^}]+)\}/g;
        const matches = htmlContent.match(mergeTagPattern);
        
        if (matches && matches.length > 0) {
            debugLog('method2-output', '❌ METHOD 2 FAILED: Merge tags still present in HTML', true);
            debugLog('method2-output', 'Found ' + matches.length + ' unprocessed merge tags:', true);
            matches.forEach(function(match) {
                debugLog('method2-output', '  - ' + match, true);
            });
            document.getElementById('method2-result').innerHTML = '<p style="color: red; font-weight: bold;">❌ Method 2 FAILED - Merge tags not processed</p>';
        } else {
            // Check if images are actually there
            const images = marqueeContainer.querySelectorAll('img');
            if (images.length > 0) {
                let hasValidImages = false;
                images.forEach(function(img) {
                    if (img.src && !img.src.includes('{acf:')) {
                        hasValidImages = true;
                        debugLog('method2-output', '✅ Found processed image: ' + img.src);
                    }
                });
                
                if (hasValidImages) {
                    debugLog('method2-output', '✅ METHOD 2 SUCCESS: Merge tags were processed');
                    document.getElementById('method2-result').innerHTML = '<p style="color: green; font-weight: bold;">✅ Method 2 WORKED! Merge tags were processed.</p>';
                } else {
                    debugLog('method2-output', '⚠️ Images found but may not be valid', true);
                }
            } else {
                debugLog('method2-output', '❌ METHOD 2 FAILED: No images found in container', true);
                document.getElementById('method2-result').innerHTML = '<p style="color: red; font-weight: bold;">❌ Method 2 FAILED - No images rendered</p>';
            }
        }
    }
    
    // Run tests
    function runTests() {
        console.log('Running tests...');
        
        // Step 1: Check data
        checkACFData();
        
        // Step 2: Test Method 1
        setTimeout(function() {
            testMethod1();
            
            // Step 3: Test Method 2 (after a delay to allow processing)
            setTimeout(function() {
                testMethod2();
                console.log('=== MERGE TAGS DEBUG TEST COMPLETE ===');
            }, 500);
        }, 100);
    }
    
    // Wait for DOM and data
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            // Wait a bit for ACF data injection
            setTimeout(runTests, 500);
        });
    } else {
        // DOM already ready, wait for ACF data
        setTimeout(runTests, 500);
    }
    
    // Also check periodically if data becomes available
    let checkCount = 0;
    const checkInterval = setInterval(function() {
        checkCount++;
        if (typeof window.dtACFData !== 'undefined' || checkCount > 20) {
            clearInterval(checkInterval);
            if (checkCount <= 20) {
                runTests();
            } else {
                debugLog('data-check-output', '⚠️ Timeout waiting for ACF data', true);
            }
        }
    }, 200);
})();
</script>
```

## What This Test Does

1. **Step 1**: Checks if `window.dtACFData` exists and shows the data structure
2. **Step 2**: Tests Method 1 (Direct Variables) - builds carousel using `window.dtACFData`
3. **Step 3**: Tests Method 2 (Merge Tags) - checks if merge tags were automatically processed

## What You'll See

- **Green sections** = Working
- **Red sections** = Not working
- **Console output** = Detailed debugging in browser console (F12)
- **Visual carousels** = Both methods will try to render images

## How to Use

1. Copy the entire code block above
2. Paste into a Divi Code Module
3. Open browser console (F12) to see detailed logs
4. Check the visual debug output on the page
5. See which method works!

The test will show you exactly what's happening and which method works for your setup.
