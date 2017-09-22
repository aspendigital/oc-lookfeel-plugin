+function ($) { "use strict";

  var MultiConditional = function (element, options) {

    var $el = this.$el = $(element),
        self = this;

    this.options = options || {};
        
    if (!this.options.match) {
      this.options.match = 'any';
    }
    
    this.fields = [];
    $.each(this.options.sources, function() {
      var field = {},
          source = this;
          
      field.selector = '[name="'+source.field+'"]';
      
      field.condition = source.field;
      if (source.condition.indexOf('value') === 0) {
        var match = source.condition.match(/[^[\]]+(?=])/g);
        field.condition = 'value';
        field.conditionValue = (match) ? match : [""];
      }
      self.fields.push(field);
      
      $(document).on('change', field.selector, $.proxy(self.onConditionChanged, self));
    });
    
    this.onConditionChanged();
  };
  
  MultiConditional.prototype.onConditionChanged = function(e) {
    var fieldResults = $.map(this.fields, function(field) {
      if (field.condition === 'checked') {
        return !!$(field.selector+':checked').length;
      }
      if (field.condition === 'unchecked') {
        return !$(field.selector+':checked').length;
      }
      if (field.condition === 'value') {
        var sourceValue = '',
            source = $(field.selector)
              .not('input[type=checkbox], input[type=radio], input[type=button], input[type=submit]');
        

        if (!source.length) {
          source = source.end()
            .not(':not(input[type=checkbox]:checked, input[type=radio]:checked)');
        }

        if (!!source.length) {
            sourceValue = source.val();
        }
        
        return $.inArray(sourceValue, field.conditionValue) > -1;
      }
      
      return false;
    });
    
    var result = this.match === 'all';
    for (var i=fieldResults.length-1; i >= 0; i--) {
      if (this.match === 'all') {
        result = result && fieldResults[i];
      }
      else {
        result = result || fieldResults[i];
      }
    }
    
    if (this.$el.prop('checked') !== result) {
      this.$el.prop('checked', result).change();
    }
  };
  
  MultiConditional.DEFAULTS = {};

  // MULTICONDITIONAL PLUGIN DEFINITION
  // ============================

  var old = $.fn.multiConditional;

  $.fn.multiConditional = function (option) {
    return this.each(function () {
      var $this = $(this);
      var data  = $this.data('oc.multiConditional');
      var options = $.extend({}, MultiConditional.DEFAULTS, $this.data('multi-conditional'), typeof option == 'object' && option);

      if (!data) $this.data('oc.multiConditional', (data = new MultiConditional(this, options)));
    });
  };

  $.fn.multiConditional.Constructor = MultiConditional;

  // MULTICONDITIONAL NO CONFLICT
  // =================

  $.fn.multiConditional.noConflict = function () {
    $.fn.multiConditional = old;
    return this;
  };

  // MULTICONDITIONAL DATA-API
  // ===============

  $(document).render(function(){
      $('[data-multi-conditional]').multiConditional();
  });

}(window.jQuery);
