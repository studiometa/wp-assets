<?php

namespace Studiometa\WP;

/**
 * Helper class to manage a theme's assets.
 */
class Assets
{

    /** @var array The parsed configuration. */
    public $config;

    /**
     * __construct
     * @param string $theme_path The absolute path to the theme.
     */
    public function __construct(string $theme_path)
    {
        $config_path = $theme_path . '/assets.yml';

        if (!file_exists($config_path)) {
            $config_path = $theme_path . '/assets.yaml';

            if (!file_exists($config_path)) {
                throw new \Exception('No assets config file found. Try adding an `assets.yml` file in your theme.', 1);
            }
        }

        $this->config = \Spyc::YAMLLoad($config_path);

        add_action('wp_enqueue_scripts', array($this, 'registerAll'));
        add_filter('template_include', array($this, 'enqueueAll'));
    }

    /**
     * Register all defined JS and CSS assets with automatic
     * versioning based on their content's MD5 hash.
     *
     * @return void
     */
    public function registerAll()
    {
        foreach ($this->config as $name => $config) {
            if (isset($config['css'])) {
                foreach ($config['css'] as $handle => $path) {
                    $this->register('style', $handle, $path);

                  // Enqueue directly if the name of the config is 'all'
                    if ($name === 'all') {
                        wp_enqueue_style($handle);
                    }
                }
            }

            if (isset($config['js'])) {
                foreach ($config['js'] as $handle => $path) {
                    $this->register('script', $handle, $path);

                    // Enqueue directly if the name of the config is 'all'
                    if ($name === 'all') {
                        wp_enqueue_script($handle);
                    }
                }
            }
        }
    }

    /**
     * Enqueue CSS and JS files based on the WordPress template.
     *
     * @param  string $template The template path.
     * @return string           The template path.
     */
    public function enqueueAll($template)
    {
        $potential_names = $this->getPotentialNames($template);

        foreach ($potential_names as $potential_name) {
            foreach ($this->config as $name => $config) {
                if ((string)$name !== $potential_name) {
                    continue;
                }

                if (isset($config['css'])) {
                    foreach ($config['css'] as $handle => $path) {
                        $this->enqueue('style', $handle);
                    }
                }

                if (isset($config['js'])) {
                    foreach ($config['js'] as $handle => $path) {
                        $this->enqueue('script', $handle);
                    }
                }
            }
        }

        return $template;
    }

    /**
     * Get all the potential assets group name.
     * For a template file `single-post-hello.php`, the following group names
     * will be returned:
     *
     * - single
     * - single-post
     * - single-post-hello
     *
     * @param  string $template  The full template path.
     */
    protected function getPotentialNames($template)
    {
        $pathinfo = pathinfo($template);
        $parts = explode('-', $pathinfo['filename']);

        return array_reduce($parts, function ($acc, $part) {
            if (empty($acc)) {
                return [$part];
            }

            $previous_part = $acc[count($acc) - 1];
            $acc[] = $previous_part . '-' . $part;

            return $acc;
        }, []);
    }


    /**
     * Register a single asset.
     *
     * @param  string $type   The type of the asset: 'style' or 'script'
     * @param  string $handle The asset's handle
     * @param  string $path   The asset's path in the theme
     * @return void
     */
    protected function register($type, $handle, $path)
    {
        if (is_array($path)) {
            $_path = $path;
            $path = $_path['path'];
            $media = $_path['media'] ?? 'all';
            $in_footer = $_path['footer'] ?? true;
        } else {
            $media = 'all';
            $in_footer = true;
        }

        $public_path = get_template_directory_uri() . '/' . $path;

        if (! file_exists(get_template_directory() . '/' . $path)) {
            throw new \Exception('The asset file "' . $path . '" does not exist.');
        }

        $hash = md5_file(get_template_directory() . '/' . $path);

        if ($type === 'style') {
            wp_register_style(
                $handle,
                $public_path,
                [],
                $hash,
                $media
            );
        } else {
            wp_register_script(
                $handle,
                $public_path,
                [],
                $hash,
                $in_footer
            );
        }
    }

    /**
     * Enqueue an asset given its handle.
     *
     * @param  string $type   The type of the asset: 'style' or 'script'
     * @param  string $handle The asset's handle
     * @return void
     */
    protected function enqueue($type, $handle)
    {
        add_action('wp_enqueue_scripts', function () use ($type, $handle) {
            if ($type === 'style') {
                wp_enqueue_style($handle);
            } else {
                wp_enqueue_script($handle);
            }
        });
    }
}
