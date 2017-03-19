String.prototype.format = function () {
    var args = arguments;
    return this.replace(/{(\d+)}/g, function (match, number) {
        return typeof args[number] != 'undefined'
            ? args[number]
            : match
            ;
    });
};
var AccordionCategoryModule = function ($) {
    var data = {
        widgetMainContainers: [],
        parentItems: [],
        lists: [],
        controls: [],
        parentToggleIcons: [],
        maxNestingLevel: 0,
        generatedStyle: null
    };
    var config = {
        CSS: {
            classes: {
                widgetMain: 'accordion-menu-widget',
                accordionList: 'accordion-list',
                accordionItem: 'accordion-list-item',
                accordionItemControls: 'accordion-list-item__controls',
                accordionItemLink: 'accordion-list-item__link',
                accordionItemPostCount: 'accordion-list-item__post-count',
                accordionItemIcon: 'accordion-list-item__icon',
                accordionItemToggleIcon: 'accordion-list-item__toggle-icon',
                isActive: 'is-active',
                isOpened: 'is-opened',
                isRoot: 'is-root',
                hasChildren: 'has-children'
            },
            dynamicClasses: {
                listLevel: '.accordion-list--level-{0}',
                paddingRightOverflow: 'padding-right : {0}px',
            },
            IDs: {
                catId: 'category-id-%d'
            },
            indentPropertyPattern: 'text-indent : {0}px;',
            paddingLeftPropertyPattern: 'padding-left : {0}px;'
        },
        regexPatterns: {
            listLevel: new RegExp('accordion-list--level-(\\d+)', 'i')
        },
        settings: {
            animationTime: 1500,
            animationType: 'easeOutSine',
            defaultPadding: 12,
            overflowCoefficient : 2.4,
            nestingTypeTextIndent: 'text-indent',
            nestingTypePadding: 'padding',
            paddingDelta: 2,
            skin: 'default'
        },
        params: accordionMenuParams
    };

    function initDefaults() {
        // Initalization default stuff
        collectWidgetElements();
        setToggleIconClickListener(_handleToggleIconClick);
        switch (config.params.nesting_type) {
            case config.settings.nestingTypePadding:
                if (config.params.auto_generate_css) {
                    _generatePaddingStylesheet();
                    _setPaddingWindowResizeHandler();
                }
                else {
                    _generateDefaultPaddingStylesheet();
                }
                break;
            case config.settings.nestingTypeTextIndent:
                if (config.params.auto_generate_css) {
                    _generateTextIndentStylesheet();
                    _setTextIndentWindowResizeHandler();
                }
                break;
            default :
                break;
        }
    }

    // Private
    var _setTextIndentWindowResizeHandler = function () {
        $(window).resize(function () {
            _removeAllGeneratedRules();
            _generateTextIndentStylesheet();
        });
    };
    var _setPaddingWindowResizeHandler = function () {
        $(window).resize(function () {
            _removeAllGeneratedRules();
            _generatePaddingStylesheet();
        });
    };
    var _addCSSRule = function addCSSRule(sheet, selector, rules, index) {
        index = index || 0;
        if ("insertRule" in sheet) {
            sheet.insertRule(selector + "{" + rules + "}", index);
        }
        else if ("addRule" in sheet) {
            sheet.addRule(selector, rules, index);
        }
    };
    var _deleteCSSRule = function deleteCSSRule(sheet, index) {
        if (sheet.cssRules) { // all browsers, except IE before version 9
            sheet.deleteRule(index);
        }
        else {
            sheet.removeRule(index);
        }
    };
    var _generateStyleElement = function () {
        // Create the <style> tag
        var style = document.createElement("style");
        // WebKit hack
        style.appendChild(document.createTextNode(""));
        // Add the <style> element to the page
        document.head.appendChild(style);
        return style;
    };
    var _handleToggleIconClick = function (e) {
        var currentItem = $(this);
        var parentControls = currentItem.parent();
        var nestedList = parentControls.nextAll('.' + config.CSS.classes.accordionList + ':first')
        parentControls.toggleClass(config.CSS.classes.isOpened);
        nestedList.toggleClass(config.CSS.classes.isOpened);
    };
    var _findMaxNestingLevel = function () {
        data.maxNestingLevel = 0;
        // Find max nesting level
        data.lists.each(function (i, el) {
            var matches = config.regexPatterns.listLevel.exec(el.className);
            if (matches != null) {
                var currentLevel = parseInt(matches[1]);
                if (currentLevel > data.maxNestingLevel) {
                    data.maxNestingLevel = currentLevel;
                }
            }
        });
        return data.maxNestingLevel;
    };
    var _calculatePaddingIndentDelta = function (maxLevel) {
        var indent = 0,
            width = 0;
        maxLevel = maxLevel || 0;
        if (maxLevel > 0) {
            // Find width of container
            width = data.widgetMainContainers.width();
            // Get 1/3 part of width in order take in account text and avoid overflow
            // This is limit of padding in other case text will overflow
            var maxWidthLimit = width / 3;
            // Find out the toggle icon size
            var toggleIconWidth = data.parentToggleIcons.width();
            // Set it as padding candidate
            indent = toggleIconWidth;
            // Check if it will fit into width limit of container
            var widthRequired = toggleIconWidth * maxLevel;
            // Calculate overflow if exists
            var overflowCoefficient = widthRequired / maxWidthLimit;
            // Check if we need to reduce padding
            if (overflowCoefficient > 1) {
                // Reduce the size of padding according to coefficient
                indent = indent / overflowCoefficient;
            }
        }
        return indent;
    };
    var _removeAllGeneratedRules = function () {
        var i = 0,
            sheet = data.generatedStyle.sheet,
            rulesLength = sheet.cssRules.length - 1;
        // Start from the end, because it shifts array while removing rule
        for (i = rulesLength; i >= 0; i--) {
            _deleteCSSRule(sheet, i);
        }
    };
    var _calculateOverflowPadding = function (level, levelSelector) {
        var width = data.widgetMainContainers.width(),
            delta = width / (data.maxNestingLevel * config.settings.overflowCoefficient);
        return {
            overflowSelector: levelSelector + ' .' + config.CSS.classes.accordionItemLink,
            overflowRule: config.CSS.dynamicClasses.paddingRightOverflow.format(delta * (level + 1))
        }
    };
    var _generateTextIndentStylesheet = function () {
        // Create the <style> tag
        var style = _generateStyleElement();
        var maxLevel = _findMaxNestingLevel();
        var indentDelta = _calculatePaddingIndentDelta(maxLevel);
        var i = 0;
        for (i = 0; i <= maxLevel; i++) {
            var levelRule = config.CSS.indentPropertyPattern.format(i * indentDelta),
                levelSelector = config.CSS.dynamicClasses.listLevel.format(i);
            if (config.params.auto_overflow_padding) {
                var overflowStyle = _calculateOverflowPadding(i, levelSelector);
                _addCSSRule(
                    style.sheet,
                    overflowStyle.overflowSelector,
                    overflowStyle.overflowRule,
                    i
                );
                _addCSSRule(
                    style.sheet,
                    levelSelector,
                    levelRule,
                    i+1
                );
            }
            else {
                _addCSSRule(
                    style.sheet,
                    levelSelector,
                    levelRule,
                    i
                );
            }

        }
        data.generatedStyle = style;
    };
    var _generateDefaultPaddingStylesheet = function () {
        // Create the <style> tag
        var style = _generateStyleElement(),
            dynStyle = config.CSS.paddingLeftPropertyPattern.format(config.settings.defaultPadding),
            dynSelector = '.' + config.CSS.classes.accordionList;
        if (config.params.auto_overflow_padding) {
            var overflowStyle = _calculateOverflowPadding(1, dynSelector);
            _addCSSRule(
                style.sheet,
                overflowStyle.overflowSelector,
                overflowStyle.overflowRule,
                0
            );
        }
        _addCSSRule(
            style.sheet,
            dynSelector,
            dynStyle,
            0
        );
        data.generatedStyle = style;
    };
    var _generatePaddingStylesheet = function () {
        // Create the <style> tag
        var style = _generateStyleElement(),
            maxLevel = _findMaxNestingLevel(),
            paddingDelta = _calculatePaddingIndentDelta(maxLevel),
            dynStyle = config.CSS.paddingLeftPropertyPattern.format(paddingDelta + config.settings.paddingDelta),
            dynSelector = '.' + config.CSS.classes.accordionList;
        if (config.params.auto_overflow_padding) {
            var overflowStyle = _calculateOverflowPadding(1, dynSelector);
            _addCSSRule(
                style.sheet,
                overflowStyle.overflowSelector,
                overflowStyle.overflowRule,
                0
            );
        }
        _addCSSRule(
            style.sheet,
            dynSelector,
            dynStyle,
            0
        )
        ;
        data.generatedStyle = style;
    };
    // Public
    var collectWidgetElements = function () {
        data.parentListItems = $(
            '.' + config.CSS.classes.widgetMain +
            ' .' + config.CSS.classes.accordionItem +
            '.' + config.CSS.classes.hasChildren
        );
        data.controls = $('.' + config.CSS.classes.accordionItemControls);
        data.parentToggleIcons = data.parentListItems
            .find('> .' + config.CSS.classes.accordionItemControls)
            .find('> .' + config.CSS.classes.accordionItemToggleIcon);
        data.widgetMainContainers = $('.' + config.CSS.classes.widgetMain);
        data.lists = $('.' + config.CSS.classes.accordionList);
    };
    var setToggleIconClickListener = function (listener) {
        data.parentToggleIcons.click(listener);
    };
    // Return object with function defined above
    return {
        collectAllWidgetData: collectWidgetElements,
        setToggleIconClickListener: setToggleIconClickListener,
        config: config,
        initDefaults: initDefaults,
        data: data
    }
};

jQuery(document).ready(function () {
    var accordionModule = AccordionCategoryModule(jQuery);
    accordionModule.initDefaults();
});
