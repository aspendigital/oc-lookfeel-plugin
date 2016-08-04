/**
 * Provide functionality to scroll October TreeView while dragging elements.
 * This has been submitted as a pull request and should be removed from the
 * Look & Feel plugin once the functionality exists in some form in core.
 * 
 * @see https://github.com/octobercms/october/pull/2277
 */
+function($) { "use strict"
    
    if (!$.fn.treeView)
        return;
    
    var TreeView = $.fn.treeView.Constructor;
    
    // Could be accepted into core
    if (TreeView.prototype.onDrag)
        return;
    
    TreeView.prototype.initSortable = function() {
        var $noDragItems = $('[data-no-drag-mode]', this.$el)

        if ($noDragItems.length > 0)
            return

        if (this.$el.data('oc.treelist'))
            this.$el.treeListWidget('unbind')

        this.$el.treeListWidget({
            tweakCursorAdjustment: this.proxy(this.tweakCursorAdjustment),
            isValidTarget: this.proxy(this.isValidTarget),
            useAnimation: false,
            usePlaceholderClone: true,
            handle: 'span.drag-handle',
            onDrag: this.proxy(this.onDrag), // ONLY DIFFERENCE FROM CORE FUNCTION
            tolerance: -20 // Give 20px of carry between containers
        })

        this.$el.on('move.oc.treelist', this.proxy(this.onNodeMove))
        this.$el.on('aftermove.oc.treelist', this.proxy(this.onAfterNodeMove))
    }
    
    var baseDispose = TreeView.prototype.dispose;
    TreeView.prototype.dispose = function() {
        baseDispose.call(this)
        this.clearScrollTimeout()
    }

    TreeView.prototype.onDrag = function ($item, position, _super, event) {
        
        this.dragCallback = function() {
            _super($item, position, null, event)
        };
        
        this.clearScrollTimeout()
        this.dragCallback()
        
        if (!this.$scrollbar || this.$scrollbar.length === 0)
            return
        
        if (position.top < 0)
            this.scrollOffset = -10 + Math.floor(position.top / 5)
        else if (position.top > this.$scrollbar.height())
            this.scrollOffset = 10 + Math.ceil((position.top - this.$scrollbar.height()) / 5)
        else
            return
        
        this.scrollMax = function() {
            return this.$el.height() - this.$scrollbar.height()
        };
        
        this.dragScroll()
    }
    
    TreeView.prototype.dragScroll = function() {
        var startScrollTop = this.$scrollbar.scrollTop()
        var changed

        this.scrollTimeout = null

        this.$scrollbar.scrollTop( Math.min(startScrollTop + this.scrollOffset, this.scrollMax()) )
        changed = this.$scrollbar.scrollTop() - startScrollTop
        if (changed === 0)
            return

        this.$el.children('ol').each(function() {
            var sortable = $(this).data('oc.sortable')

            sortable.refresh()
            sortable.cursorAdjustment.top -= changed // Keep cursor adjustment in sync with scroll
        });

        this.dragCallback()
        this.$scrollbar.data('oc.scrollbar').setThumbPosition() // Update scrollbar position
        
        this.scrollTimeout = window.setTimeout(this.proxy(this.dragScroll), 100)
    }
    
    TreeView.prototype.clearScrollTimeout = function() {
        if (this.scrollTimeout) {
            window.clearTimeout(this.scrollTimeout)
            this.scrollTimeout = null
        }
    }
    
}(window.jQuery);