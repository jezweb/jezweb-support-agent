<?php
/**
 * Elementor Data Parser
 * Parses Elementor JSON and creates navigation guides for AI agent
 */

if (!defined('ABSPATH')) {
    exit;
}

class Jezweb_Support_Elementor_Parser {

    /**
     * Parse Elementor structure and return simplified tree
     */
    public function parse_elementor_structure($elementor_data) {
        if (empty($elementor_data) || !is_array($elementor_data)) {
            return array();
        }

        $structure = array();
        foreach ($elementor_data as $index => $element) {
            $structure[] = $this->parse_element($element, $index + 1);
        }

        return $structure;
    }

    /**
     * Parse individual element
     */
    private function parse_element($element, $position = 1, $depth = 0) {
        if (empty($element) || !is_array($element)) {
            return null;
        }

        $parsed = array(
            'id' => isset($element['id']) ? $element['id'] : '',
            'type' => isset($element['elType']) ? $element['elType'] : 'unknown',
            'widget_type' => isset($element['widgetType']) ? $element['widgetType'] : null,
            'position' => $position,
            'depth' => $depth,
        );

        // Extract settings
        if (isset($element['settings']) && is_array($element['settings'])) {
            $parsed['settings'] = $this->extract_important_settings($element['settings']);
        }

        // Extract content from specific widget types
        if ($parsed['widget_type']) {
            $parsed['content'] = $this->extract_widget_content($element);
        }

        // Process children
        if (isset($element['elements']) && is_array($element['elements'])) {
            $parsed['children'] = array();
            foreach ($element['elements'] as $child_index => $child) {
                $child_parsed = $this->parse_element($child, $child_index + 1, $depth + 1);
                if ($child_parsed) {
                    $parsed['children'][] = $child_parsed;
                }
            }
        }

        return $parsed;
    }

    /**
     * Extract important settings from element
     */
    private function extract_important_settings($settings) {
        $important = array();

        // Common settings to extract
        $keys_to_extract = array(
            // Background
            'background_background', 'background_color', 'background_image',
            // Colors
            'color', 'text_color', 'heading_color', 'link_color',
            // Typography
            'typography_font_family', 'typography_font_size', 'typography_font_weight',
            // Spacing
            'margin', 'padding', 'border_radius',
            // Text content
            'title', 'text', 'editor', 'html',
            // Button
            'button_text', 'link',
            // Image
            'image', 'alt_text',
        );

        foreach ($keys_to_extract as $key) {
            if (isset($settings[$key]) && !empty($settings[$key])) {
                $important[$key] = $settings[$key];
            }
        }

        return $important;
    }

    /**
     * Extract content from widget based on type
     */
    private function extract_widget_content($element) {
        $widget_type = isset($element['widgetType']) ? $element['widgetType'] : '';
        $settings = isset($element['settings']) ? $element['settings'] : array();

        $content = array();

        switch ($widget_type) {
            case 'heading':
                $content['text'] = isset($settings['title']) ? $settings['title'] : '';
                $content['html_tag'] = isset($settings['header_size']) ? $settings['header_size'] : 'h2';
                break;

            case 'text-editor':
                $content['text'] = isset($settings['editor']) ? wp_strip_all_tags($settings['editor']) : '';
                break;

            case 'button':
                $content['text'] = isset($settings['text']) ? $settings['text'] : '';
                $content['link'] = isset($settings['link']['url']) ? $settings['link']['url'] : '';
                break;

            case 'image':
                $content['url'] = isset($settings['image']['url']) ? $settings['image']['url'] : '';
                $content['alt'] = isset($settings['image']['alt']) ? $settings['image']['alt'] : '';
                break;

            case 'html':
                $content['html'] = isset($settings['html']) ? $settings['html'] : '';
                break;
        }

        return $content;
    }

    /**
     * Build navigation tree for easy editing guidance
     */
    public function build_navigation_tree($elementor_data) {
        if (empty($elementor_data) || !is_array($elementor_data)) {
            return array();
        }

        $navigation = array();
        foreach ($elementor_data as $section_index => $section) {
            $navigation[] = $this->build_navigation_path($section, array("Section " . ($section_index + 1)));
        }

        return $navigation;
    }

    /**
     * Build navigation path for element
     */
    private function build_navigation_path($element, $path = array()) {
        if (empty($element) || !is_array($element)) {
            return null;
        }

        $current_path = $path;
        $element_type = isset($element['elType']) ? $element['elType'] : 'element';
        $widget_type = isset($element['widgetType']) ? $element['widgetType'] : null;
        $element_id = isset($element['id']) ? $element['id'] : '';

        // Add current element to path
        if ($widget_type) {
            $label = $this->get_widget_label($element);
            $current_path[] = $label . " (ID: " . $element_id . ")";
        } elseif ($element_type === 'container') {
            $current_path[] = "Container (ID: " . $element_id . ")";
        } elseif ($element_type === 'column') {
            $current_path[] = "Column (ID: " . $element_id . ")";
        }

        $nav_item = array(
            'path' => implode(' → ', $current_path),
            'element_id' => $element_id,
            'type' => $element_type,
            'widget_type' => $widget_type,
        );

        // Add content preview if widget
        if ($widget_type) {
            $content = $this->extract_widget_content($element);
            if (!empty($content)) {
                $nav_item['content_preview'] = $this->get_content_preview($content);
            }
        }

        $result = array($nav_item);

        // Process children
        if (isset($element['elements']) && is_array($element['elements'])) {
            foreach ($element['elements'] as $child_index => $child) {
                $child_nav = $this->build_navigation_path($child, $current_path);
                if ($child_nav) {
                    $result = array_merge($result, $child_nav);
                }
            }
        }

        return $result;
    }

    /**
     * Get friendly label for widget
     */
    private function get_widget_label($element) {
        $widget_type = isset($element['widgetType']) ? $element['widgetType'] : 'Widget';
        $settings = isset($element['settings']) ? $element['settings'] : array();

        $labels = array(
            'heading' => 'Heading',
            'text-editor' => 'Text Editor',
            'button' => 'Button',
            'image' => 'Image',
            'spacer' => 'Spacer',
            'divider' => 'Divider',
            'html' => 'HTML',
            'icon' => 'Icon',
            'video' => 'Video',
        );

        $label = isset($labels[$widget_type]) ? $labels[$widget_type] : ucwords(str_replace('-', ' ', $widget_type));

        // Add content preview to label for heading and button
        if ($widget_type === 'heading' && isset($settings['title'])) {
            $preview = wp_trim_words(wp_strip_all_tags($settings['title']), 5);
            $label .= ' "' . $preview . '"';
        } elseif ($widget_type === 'button' && isset($settings['text'])) {
            $label .= ' "' . $settings['text'] . '"';
        }

        return $label;
    }

    /**
     * Get content preview
     */
    private function get_content_preview($content) {
        if (isset($content['text'])) {
            return wp_trim_words(wp_strip_all_tags($content['text']), 10);
        }
        if (isset($content['html'])) {
            return wp_trim_words(wp_strip_all_tags($content['html']), 10);
        }
        return '';
    }

    /**
     * Extract all editable content mapped to widget IDs
     */
    public function extract_editable_content($elementor_data) {
        if (empty($elementor_data) || !is_array($elementor_data)) {
            return array();
        }

        $editable = array();
        foreach ($elementor_data as $element) {
            $this->collect_editable_content($element, $editable);
        }

        return $editable;
    }

    /**
     * Recursively collect editable content
     */
    private function collect_editable_content($element, &$editable) {
        if (empty($element) || !is_array($element)) {
            return;
        }

        $element_id = isset($element['id']) ? $element['id'] : '';
        $widget_type = isset($element['widgetType']) ? $element['widgetType'] : null;

        if ($widget_type && $element_id) {
            $content = $this->extract_widget_content($element);
            if (!empty($content)) {
                $editable[$element_id] = array(
                    'widget_type' => $widget_type,
                    'content' => $content,
                );
            }
        }

        // Process children
        if (isset($element['elements']) && is_array($element['elements'])) {
            foreach ($element['elements'] as $child) {
                $this->collect_editable_content($child, $editable);
            }
        }
    }

    /**
     * Count total widgets
     */
    public function count_widgets($elementor_data) {
        if (empty($elementor_data) || !is_array($elementor_data)) {
            return 0;
        }

        $count = 0;
        foreach ($elementor_data as $element) {
            $count += $this->count_element_widgets($element);
        }

        return $count;
    }

    /**
     * Count widgets in element
     */
    private function count_element_widgets($element) {
        if (empty($element) || !is_array($element)) {
            return 0;
        }

        $count = 0;

        // Count if it's a widget
        if (isset($element['widgetType'])) {
            $count = 1;
        }

        // Count children
        if (isset($element['elements']) && is_array($element['elements'])) {
            foreach ($element['elements'] as $child) {
                $count += $this->count_element_widgets($child);
            }
        }

        return $count;
    }
}
