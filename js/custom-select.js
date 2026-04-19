// Custom dropdown functionality
document.addEventListener('DOMContentLoaded', function() {
    // Convert all form-control selects to custom dropdowns
    const selects = document.querySelectorAll('select.form-control');
    
    selects.forEach((select, index) => {
        // Create wrapper
        const wrapper = document.createElement('div');
        wrapper.className = 'custom-select-wrapper';
        wrapper.style.position = 'relative';
        wrapper.style.width = '100%';
        wrapper.style.maxWidth = '100%';
        
        // Create custom select button
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'custom-select-button form-control';
        button.innerHTML = (select.options[select.selectedIndex].text || 'Select...') + ' <span style="font-size: 0.8rem; margin-left: auto;">▼</span>';
        button.style.textAlign = 'left';
        button.style.display = 'flex';
        button.style.justifyContent = 'space-between';
        button.style.alignItems = 'center';
        button.style.width = '100%';
        
        // Create dropdown menu
        const menu = document.createElement('div');
        menu.className = 'custom-select-menu';
        menu.style.display = 'none';
        menu.style.position = 'absolute';
        menu.style.top = '100%';
        menu.style.left = '0';
        menu.style.right = '0';
        menu.style.backgroundColor = '#0f1628';
        menu.style.border = '1.5px solid rgba(0, 212, 255, 0.3)';
        menu.style.borderRadius = '4px';
        menu.style.maxHeight = '300px';
        menu.style.overflowY = 'auto';
        menu.style.zIndex = '2000';
        menu.style.marginTop = '4px';
        menu.style.boxShadow = '0 8px 16px rgba(0,0,0,0.4)';
        menu.style.minWidth = '100%';
        
        // Add options to menu
        Array.from(select.options).forEach((option, idx) => {
            const item = document.createElement('div');
            item.className = 'custom-select-item';
            item.textContent = option.text;
            item.dataset.index = idx;
            item.style.padding = '12px 16px';
            item.style.cursor = 'pointer';
            item.style.backgroundColor = '#0f1628';
            item.style.color = '#e0e0e0';
            item.style.borderBottom = '1px solid rgba(0, 212, 255, 0.1)';
            item.style.whiteSpace = 'nowrap';
            item.style.overflow = 'hidden';
            item.style.textOverflow = 'ellipsis';
            
            if (option.selected) {
                item.style.backgroundColor = '#00d4ff';
                item.style.color = '#ffffff';
                item.style.fontWeight = '600';
            }
            
            function highlightItem() {
                // Reset all items
                menu.querySelectorAll('.custom-select-item').forEach(el => {
                    if (!select.options[el.dataset.index].selected) {
                        el.style.backgroundColor = '#0f1628';
                        el.style.color = '#e0e0e0';
                    }
                });
                // Highlight this item
                if (!option.selected) {
                    item.style.backgroundColor = '#1a2540';
                }
            }

            function unhighlightItem() {
                if (!option.selected) {
                    item.style.backgroundColor = '#0f1628';
                }
            }
            
            item.addEventListener('mouseover', highlightItem);
            item.addEventListener('touchstart', highlightItem);
            
            item.addEventListener('mouseout', unhighlightItem);
            item.addEventListener('touchend', function() {
                unhighlightItem();
            });
            
            item.addEventListener('touchmove', function(e) {
                e.preventDefault();
            });
            
            item.addEventListener('click', function(e) {
                e.stopPropagation();
                select.selectedIndex = idx;
                button.innerHTML = option.text + ' <span style="font-size: 0.8rem; margin-left: auto;">▼</span>';
                
                // Update all selected items visualization
                menu.querySelectorAll('.custom-select-item').forEach((el, i) => {
                    if (select.options[i].selected) {
                        el.style.backgroundColor = '#00d4ff';
                        el.style.color = '#ffffff';
                        el.style.fontWeight = '600';
                    } else {
                        el.style.backgroundColor = '#0f1628';
                        el.style.color = '#e0e0e0';
                        el.style.fontWeight = '400';
                    }
                });
                
                menu.style.display = 'none';
                
                // Trigger change event
                select.dispatchEvent(new Event('change', { bubbles: true }));
                button.focus();
            });
            
            menu.appendChild(item);
        });
        
        // Toggle menu visibility
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const isOpen = menu.style.display !== 'none';
            menu.style.display = isOpen ? 'none' : 'block';
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!wrapper.contains(e.target)) {
                menu.style.display = 'none';
            }
        });
        
        // Build wrapper
        wrapper.appendChild(button);
        wrapper.appendChild(menu);
        
        // Replace original select display with custom one but KEEP the select in DOM for form submission
        select.style.display = 'none';
        select.parentNode.insertBefore(wrapper, select);
        // DON'T remove the select - keep it hidden so form submission works
    });
});

