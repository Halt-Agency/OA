/**
 * Client-side Merge Tag Processor for Divi Code Modules
 * Fallback when server-side processing doesn't work
 */
(function() {
    'use strict';
    
    // Wait for DOM to be ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', processMergeTags);
    } else {
        processMergeTags();
    }
    
    function processMergeTags() {
        // Find all elements that might contain merge tags
        const codeModules = document.querySelectorAll('.et_pb_code, .et_pb_code_inner, [class*="et_pb_code"]');
        
        codeModules.forEach(function(module) {
            let content = module.innerHTML;
            const mergeTagPattern = /\{([^}]+)\}/g;
            const matches = content.match(mergeTagPattern);
            
            if (!matches || matches.length === 0) {
                return; // No merge tags found
            }
            
            // Use injected ACF data if available (faster and more reliable)
            let acfData = window.dtACFData || null;
            const postId = window.dtPostId || getPostId();
            
            if (acfData) {
                // Process merge tags with injected data
                matches.forEach(function(match) {
                    const tag = match.replace(/[{}]/g, '');
                    const value = getMergeTagValue(tag, acfData);
                    if (value) {
                        content = content.replace(new RegExp(escapeRegex(match), 'g'), value);
                    }
                });
                module.innerHTML = content;
            } else if (postId) {
                // Fallback: Fetch ACF data via REST API
                fetchACFData(postId).then(function(apiData) {
                    matches.forEach(function(match) {
                        const tag = match.replace(/[{}]/g, '');
                        const value = getMergeTagValue(tag, apiData);
                        if (value) {
                            content = content.replace(new RegExp(escapeRegex(match), 'g'), value);
                        }
                    });
                    module.innerHTML = content;
                }).catch(function(error) {
                    console.error('Merge Tags: Error fetching ACF data', error);
                });
            } else {
                console.warn('Merge Tags: Could not determine post ID or ACF data');
            }
        });
    }
    
    function getPostId() {
        // Try multiple methods to get post ID
        const body = document.body;
        
        // Method 1: Data attribute
        if (body.dataset.postId) {
            return parseInt(body.dataset.postId);
        }
        
        // Method 2: Body class
        const bodyClasses = body.className.match(/postid-(\d+)/);
        if (bodyClasses) {
            return parseInt(bodyClasses[1]);
        }
        
        // Method 3: Try to get from current URL
        const urlMatch = window.location.href.match(/\/wp-admin\/post\.php\?post=(\d+)/);
        if (urlMatch) {
            return parseInt(urlMatch[1]);
        }
        
        // Method 4: Try REST API to get current post
        return null;
    }
    
    function fetchACFData(postId) {
        // Try ACF REST API endpoint first
        return fetch('/wp-json/acf/v3/pages/' + postId)
            .then(function(response) {
                if (response.ok) {
                    return response.json();
                }
                // Fallback to standard REST API
                return fetch('/wp-json/wp/v2/pages/' + postId).then(function(r) {
                    return r.json();
                });
            })
            .then(function(data) {
                // Return ACF data in a consistent format
                return data.acf || data;
            });
    }
    
    function getMergeTagValue(tag, acfData) {
        // ACF Fields: {acf:field_name} or {acf:repeater:row:sub_field}
        if (tag.indexOf('acf:') === 0) {
            const fieldPath = tag.replace('acf:', '');
            
            // Check if repeater field
            if (fieldPath.indexOf(':') !== -1) {
                const parts = fieldPath.split(':');
                const repeaterName = parts[0];
                const rowIndex = parseInt(parts[1]) || 0;
                const subField = parts[2] || '';
                const property = parts[3] || '';
                
                if (acfData[repeaterName] && acfData[repeaterName][rowIndex]) {
                    const row = acfData[repeaterName][rowIndex];
                    if (row[subField]) {
                        const subValue = row[subField];
                        if (property && subValue[property]) {
                            return subValue[property];
                        } else if (subValue.url) {
                            return subValue.url;
                        }
                        return subValue;
                    }
                }
            } else {
                // Regular ACF field
                const fieldValue = acfData[fieldPath];
                if (fieldValue) {
                    if (fieldValue.url) {
                        return fieldValue.url;
                    }
                    return fieldValue;
                }
            }
        }
        
        // Post data tags
        switch(tag) {
            case 'post_title':
                return document.querySelector('h1.entry-title, .entry-title')?.textContent || '';
            case 'site_url':
                return window.location.origin;
            case 'site_name':
                return document.querySelector('title')?.textContent || '';
        }
        
        return null;
    }
    
    function escapeRegex(str) {
        return str.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }
})();
