##Aspen Digital Look & Feel

An OctoberCMS plugin to override default behavior for a better user experience.

To install, clone this repository or create a Git submodule at `/plugins/aspendigital/lookfeel/`

###Core OctoberCMS

This plugin modifies the following functionality in the OctoberCMS core.

###Pages Plugin

This plugin adds the following functionality to the Pages plugin, generally by setting configuration variables on theme layouts.

####Sub-page layout inheritance
When adding a sub-page, the new page will default to the parent page's layout.

####Sub-page URL inheritance
When changing the URL of a parent page, its existing children will pick up the URL change (if using that URL as a prefix).

####Content tabs for variables
Any variable fields will appear in tabs next to the content tab and any placeholders instead of next to the page settings and metadata. In other words, this plugin moves these fields from the primary tabs to the secondary tabs.

####Set a default layout
In a layout file's configuration section, set
`default = true`

New pages that don't inherit from a parent page will default to this layout.

####Hide layout content markup field
In a layout file's configuration section, set
`hideContentField = true`

####Hide layout in dropdown list
In a layout file's configuration section, set
`hidden = true`

This plugin adds a permission for viewing hidden elements. Hidden layouts are visible to super users and those granted this permission. Also, if an existing page uses an otherwise-hidden layout, the layout will not be hidden in the dropdown list.
