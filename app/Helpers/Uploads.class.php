<?php
namespace app\Helpers;

class Uploads extends UploadHandler
{
    public static function run()
    {
        error_reporting(E_ALL | E_STRICT);
        new Uploads();
    }

    public function __construct($options = null, $initialize = true, $error_messages = null)
    {
        $tmp_options = array(
            'script_url' => $this->get_full_url() . '/' . $this->basename($this->get_server_var('SCRIPT_NAME')) . $this->get_server_var('PATH_INFO'),
            'upload_dir' => realpath($this->get_server_var('DOCUMENT_ROOT') . '/img_rest/') . DIRECTORY_SEPARATOR,
            'upload_url' => '/img_rest/',
            'sub_directory' => '',
            'delete_type' => 'POST',
            'image_versions' => array(
                // The empty image version key defines options for the original image.
                // Keep in mind: these image manipulations are inherited by all other image versions from this point onwards.
                // Also note that the property 'no_cache' is not inherited, since it's not a manipulation.
                '' => array(
                    // Automatically rotate images based on EXIF meta data:
                    'auto_orient' => true,
                    'jpeg_quality' => 90,
                    // Set watermark
                    'watermark' => array(
                        'text' => "www.sudak.ru",
                        'font_file' => 'timesbd.ttf',
                        'darkness' => 70 // уровень затемнения знака
                    )
                ),
                // You can add arrays to generate different versions.
                // The name of the key is the name of the version (example: 'medium').
                // the array contains the options to apply.
                /*
                'medium' => array(
                    'max_width' => 800,
                    'max_height' => 600
                ),
		        */
                'sm' => array(
                    // Uncomment the following to use a defined directory for the thumbnails
                    // instead of a subdirectory based on the version identifier.
                    // Make sure that this directory doesn't allow execution of files if you
                    // don't pose any restrictions on the type of uploaded files, e.g. by
                    // copying the .htaccess file from the files directory for Apache:
                    //'upload_dir' => dirname($this->get_server_var('SCRIPT_FILENAME')).'/thumb/',
                    //'upload_url' => $this->get_full_url().'/thumb/',
                    // Uncomment the following to force the max
                    // dimensions and e.g. create square thumbnails:
                    // 'auto_orient' => true,
                    'crop' => true,
                    'jpeg_quality' => 90,
                    // 'no_cache' => true, (there's a caching option, but this remembers thumbnail sizes from a previous action!)
                    'strip' => true, // (this strips EXIF tags, such as geolocation)
                    'max_width' => 338, // either specify width, or set to 0. Then width is automatically adjusted - keeping aspect ratio to a specified max_height.
                    'max_height' => 255, // either specify height, or set to 0. Then height is automatically adjusted - keeping aspect ratio to a specified max_width.
                    // Set watermark
                    'watermark' => array(
                        'text' => "www.sudak.ru",
                        'font_file' => 'timesbd.ttf',
                        'darkness' => 70 // уровень затемнения знака
                    )
                )
            ),
            'image_library' => 0, // use GD library
        );
        // process sub_directory
        $subDirectory = '';
        if ($this->get_query_param('sub_directory')) {
            $subDirectory = $this->get_query_param('sub_directory');
        } elseif ($this->get_post_param('sub_directory')) {
            $subDirectory = $this->get_post_param('sub_directory');
        }
        if (!empty($subDirectory)) {
            $subDirectory = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $subDirectory);
            if (file_exists($tmp_options['upload_dir'] . $subDirectory)) {
                $subDirectory = realpath($tmp_options['upload_dir'] . $subDirectory);
            } else {
                $subDirectory = $this->get_absolute_path($tmp_options['upload_dir'] . $subDirectory);
            }
            if ($subDirectory === false) {
                $subDirectory = '';
            } else {
                if (stripos($subDirectory, $tmp_options['upload_dir']) !== 0) {
                    // out of website directory
                    $subDirectory = '';
                } else {
                    $subDirectory = substr($subDirectory, mb_strlen($tmp_options['upload_dir']));
                }
            }
            $tmp_options['sub_directory'] = $subDirectory . DIRECTORY_SEPARATOR;
            $tmp_options['upload_dir'] .= $subDirectory . DIRECTORY_SEPARATOR;
            $tmp_options['upload_url'] .= str_replace(DIRECTORY_SEPARATOR, '/', $subDirectory) . '/';
        }

        // by default watermark not set
        $setWatermark = false;
        if ($this->get_post_param('set_watermark')) {
            $setWatermark = $this->get_post_param('set_watermark') === 'y';
        }
        if (!$setWatermark) {
            foreach ($tmp_options['image_versions'] as $key => $version) {
                $tmp_options['image_versions'][$key]['watermark'] = array();
            }
        }

        if ($options) {
            $tmp_options = $options + $tmp_options;
        }
        parent::__construct($tmp_options, $initialize, $error_messages);
    }

    protected function set_additional_file_properties($file)
    {
        $file->deleteUrl = $this->options['script_url']
            . $this->get_query_separator($this->options['script_url'])
            . 'sub_directory=' . rawurlencode($this->options['sub_directory']) . '&'
            . $this->get_singular_param_name()
            . '=' . rawurlencode($file->name);
        $file->deleteType = $this->options['delete_type'];
        if ($file->deleteType !== 'DELETE') {
            $file->deleteUrl .= '&_method=DELETE';
        }
        if ($this->options['access_control_allow_credentials']) {
            $file->deleteWithCredentials = true;
        }

        // get image width + height
        $file_path = $this->get_upload_path($file->name);
        if ($this->is_valid_image_file($file_path)) {
            $size = getimagesize($file_path);
            if ($size !== false) {
                $file->width = $size[0];
                $file->height = $size[1];
            }
        }
    }

    public function delete($print_response = true)
    {
        $file_names = $this->get_file_names_params();
        if (empty($file_names)) {
            $file_names = array($this->get_file_name_param());
        }
        $response = array();
        foreach ($file_names as $file_name) {
            $file_path = '';
            $success = false;
            if ($file_name[0] !== '.') {
                $file_path = $this->get_upload_path($file_name);
                if (!is_file($file_path)) {
                    // файла уже нет
                    $success = true;
                } else {
                    $success = unlink($file_path);
                }
            }
            if ($success) {
                // удалим файл json
                if (is_file($file_path . ".json")) {
                    unlink($file_path . ".json");
                }
                foreach ($this->options['image_versions'] as $version => $options) {
                    if (!empty($version)) {
                        $file = $this->get_upload_path($file_name, $version);
                        if (is_file($file)) {
                            unlink($file);
                        }
                        // remove empty directory
                        $dir = $this->get_upload_path('', $version);
                        if (is_dir($dir)) {
                            $filesInDir = scandir($dir);
                            if ($filesInDir !== false && count($filesInDir) === 2) {
                                rmdir(realpath($dir));
                            }
                        }
                    }
                }
            }
            $response[$file_name] = $success;
            if (!$success) {
                $response['message'] = "File " . $file_name . " isn't deleted!";
                $this->http_response(400, json_encode($response), 'text/json');
            }
        }
        return $this->generate_response($response, $print_response);
    }

    public function http_header_code_verbose($code)
    {
        switch ($code):
            case 200:
                return '200 OK';
            case 201:
                return '201 Created';
            case 204:
                return '204 No Content';
            case 205:
                return '205 Reset Content';
            case 400:
                return '400 Bad Request';
            case 401:
                return '401 Unauthorized';
            case 403:
                return '403 Forbidden';
            case 404:
                return '404 Not Found';
            case 405:
                return '405 Method Not Allowed';
            case 416:
                return '416 Requested Range Not Satisfiable';
            case 418:
                return "418 I'm a teapot";
            case 422:
                return '422 Unprocessable Entity';
            default:
                return '500 Internal Server Error';
        endswitch;
    }

    public function http_response($code, $content = false, $contentType = 'text/html', $charset = 'UTF-8')
    {

        header('HTTP/1.1 ' . $this->http_header_code_verbose($code));
        header('Status: ' . $this->http_header_code_verbose($code) . "\r\n");
        header("Connection: Close\r\n");
        $ct = "Content-Type: $contentType";
        if ($charset)
            $ct .= "; charset=$charset";
        header($ct);
        if ($content) {
            header('Content-Length: ' . strlen($content) . "\r\n\r\n");
            print $content;
            exit;
        }
    }

    protected function get_unique_filename($file_path, $name, $size, $type, $error,
                                           $index, $content_range)
    {
        while (is_dir($this->get_upload_path($name))) {
            $name = $this->get_new_name($name);
        }
        // Keep an existing filename if this is part of a chunked upload:
        $uploaded_bytes = $this->fix_integer_overflow((int)$content_range[1]);
        if (is_file($this->get_upload_path($name)) &&
            $uploaded_bytes === $this->get_file_size(
                $this->get_upload_path($name))) {
            return $name;
        }
        $name = $this->get_new_name($name);
        while (is_file($this->get_upload_path($name))) {
            $name = $this->get_new_name($name);
        }
        return $name;
    }

    protected function handle_file_upload($uploaded_file, $name, $size, $type, $error,
                                          $index = null, $content_range = null)
    {
        $old_name = pathinfo($name, PATHINFO_FILENAME);;
        $file = parent::handle_file_upload($uploaded_file, $name, $size, $type, $error, $index, $content_range);
        $file->title = $old_name ? $old_name : '';
        $file_path = $this->get_upload_path($file->name);

        // extract file npp from title
        $file->npp = '';
        if ($file->title) {
            $match = array();
            if (preg_match('/^([\d|\.]*)\s+(.*)$/', $file->title, $match)) {
                if ($match[1]) {
                    $file->npp = $match[1];
                }
                if ($match[2]) {
                    $file->title = trim($match[2]);
                }
            }
        }

        if ($this->is_valid_image_file($file_path)) {
            $this->update_file_json($file);
        }
        return $file;
    }

    protected function read_file_json($file)
    {
        // прочитаем существующий json, если есть
        $file_path = $this->get_upload_path($file->name);
        $json_content = @file_get_contents($file_path . ".json");
        $metadata = array();
        if ($json_content) {
            $metadata = json_decode($json_content, true);
        }
        return $metadata;
    }

    protected function update_file_json($file)
    {
        $file_path = $this->get_upload_path($file->name);
        // проверим, есть ли исходный файл. если его нет - надо удалить его json
        if (!is_file($file_path)) {
            if (is_file($file_path . ".json")) {
                unlink($file_path . ".json");
            }
            return;
        }

        $metadata = $this->read_file_json($file);
        $exifData = exif_read_data($file_path, 'EXIF', true);
        $dt = null;
        // дата фотографирования
        if ($exifData && isset($exifData['EXIF']) && isset($exifData['EXIF']['DateTimeOriginal'])) {
            $dt = date_create_from_format("Y:m:d H:i:s", $exifData['EXIF']['DateTimeOriginal']);
            if ($dt) {
                $dt->setTimezone(new DateTimeZone("UTC"));
                $metadata['photoTakenTime']['timestamp'] = $dt->getTimestamp();
                $metadata['photoTakenTime']['formatted'] = $dt->format("d M. Y, H:i:s T");
            }
        }
        if (!$dt) {
            $dt = new DateTime();
        }
        $dt->setTimezone(new DateTimeZone('Europe/Moscow'));
        // дата создания файла
        $stat = stat($file_path);
        if ($stat && isset($stat['ctime'])) {
            $dt->setTimestamp($stat['ctime']);
            if ($dt) {
                $metadata['creationTime']['timestamp'] = $dt->getTimestamp();
                $metadata['creationTime']['formatted'] = $dt->format("d M. Y, H:i:s T");
            }
        }
        // дата модификации файла
        if ($stat && isset($stat['mtime'])) {
            $dt->setTimestamp($stat['mtime']);
            if ($dt) {
                $metadata['modificationTime']['timestamp'] = $dt->getTimestamp();
                $metadata['modificationTime']['formatted'] = $dt->format("d M. Y, H:i:s T");
            }
        }
        //
        $metadata['title'] = $file->name;
        $metadata['description'] = $file->title;
        $metadata['url'] = $file->url;

        file_put_contents($file_path . ".json", json_encode($metadata));
    }

    protected function get_new_name($name)
    {
        $ext = pathinfo($name, PATHINFO_EXTENSION);
        $name = mb_substr(uniqid(), 0, 8) . ($ext ? "." . $ext : "");
        return mb_strtolower($name);
    }

    public function post($print_response = true)
    {
        if ($this->get_post_param('_method') === 'JSON') {
            return $this->save_descriptions_info($print_response);
        }
        return parent::post($print_response);
    }

    protected function gd_create_scaled_image($file_name, $version, $options)
    {
        if (!function_exists('imagecreatetruecolor')) {
            error_log('Function not found: imagecreatetruecolor');
            return false;
        }
        list($file_path, $new_file_path) =
            $this->get_scaled_image_file_paths($file_name, $version);
        $type = strtolower(substr(strrchr($file_name, '.'), 1));
        switch ($type) {
            case 'jpg':
            case 'jpeg':
                $src_func = 'imagecreatefromjpeg';
                $write_func = 'imagejpeg';
                $image_quality = isset($options['jpeg_quality']) ?
                    $options['jpeg_quality'] : 75;
                break;
            case 'gif':
                $src_func = 'imagecreatefromgif';
                $write_func = 'imagegif';
                $image_quality = null;
                break;
            case 'png':
                $src_func = 'imagecreatefrompng';
                $write_func = 'imagepng';
                $image_quality = isset($options['png_quality']) ?
                    $options['png_quality'] : 9;
                break;
            default:
                return false;
        }
        $src_img = $this->gd_get_image_object(
            $file_path,
            $src_func,
            !empty($options['no_cache'])
        );
        $image_oriented = false;
        if (!empty($options['auto_orient']) && $this->gd_orient_image(
                $file_path,
                $src_img
            )) {
            $image_oriented = true;
            $src_img = $this->gd_get_image_object(
                $file_path,
                $src_func
            );
        }
        $max_width = $img_width = imagesx($src_img);
        $max_height = $img_height = imagesy($src_img);
        if (!empty($options['max_width'])) {
            $max_width = $options['max_width'];
        }
        if (!empty($options['max_height'])) {
            $max_height = $options['max_height'];
        }
        $is_vertical_image = ($img_height / $img_width) > 1;
        if ($is_vertical_image) {
            $i = $max_width;
            $max_width = $max_height;
            $max_height = $i;
        }

        $x_ratio = $max_width / $img_width;
        $y_ratio = $max_height / $img_height;
        $ratio = max($x_ratio, $y_ratio);

        if ($ratio >= 1) {
            $success = true;
            if ($image_oriented) {
                $success = $write_func($src_img, $new_file_path, $image_quality);
            } elseif ($file_path !== $new_file_path) {
                $success = copy($file_path, $new_file_path);
            }
            if (!empty($options['watermark']) && $success) {
                // watermark image & save
                $success = $this->gd_watermark_image(
                    $new_file_path,
                    $src_func,
                    $write_func,
                    $image_quality,
                    $options['watermark']
                );
            }
            return $success;
        }
        if (empty($options['crop'])) {
            $new_width = $img_width * $ratio;
            $new_height = $img_height * $ratio;
            $dst_x = 0;
            $dst_y = 0;
            $new_img = imagecreatetruecolor($new_width, $new_height);
        } else {
            $use_x_ratio = ($x_ratio == $ratio);
            $new_width = $use_x_ratio ? $max_width : floor($img_width * $ratio);
            $new_height = !$use_x_ratio ? $max_height : floor($img_height * $ratio);
            $dst_x = $use_x_ratio ? 0 : floor(($max_width - $new_width) / 2);
            $dst_y = !$use_x_ratio ? 0 : floor(($max_height - $new_height) / 2);

            $new_img = imagecreatetruecolor($max_width, $max_height);
        }
        // Handle transparency in GIF and PNG images:
        switch ($type) {
            case 'gif':
            case 'png':
                imagecolortransparent($new_img, imagecolorallocate($new_img, 0, 0, 0));
            case 'png':
                imagealphablending($new_img, false);
                imagesavealpha($new_img, true);
                break;
        }
        $success = imagecopyresampled(
            $new_img,
            $src_img,
            $dst_x,
            $dst_y,
            0,
            0,
            $new_width,
            $new_height,
            $img_width,
            $img_height
        );
        $success = $success && $write_func($new_img, $new_file_path, $image_quality);
        imagedestroy($new_img);
        if ($success && !empty($options['watermark'])) {
            $success = $this->gd_watermark_image(
                $new_file_path,
                $src_func,
                $write_func,
                $image_quality,
                $options['watermark']
            );
        }
        //$this->gd_set_image_object($file_path, $new_img);
        return $success;
    }

    protected function gd_watermark_image($file_path, $src_func, $write_func, $image_quality, $watermark_options)
    {
        try {
            // select font
            $font = $watermark_options['font_file'] ? $watermark_options['font_file'] : 'timesbd.ttf';
            $font_file = realpath(@$this->get_server_var('DOCUMENT_ROOT') . DIRECTORY_SEPARATOR . "fonts" . DIRECTORY_SEPARATOR . $font);
            if ($font_file === false || !is_file($font_file)) {
                return false;
            }

            $new_img = $src_func($file_path);

            $imageheight = imagesy($new_img);

            // наложение водного знака
            $darkness = $watermark_options['darkness'] ? $watermark_options['darkness'] : 70; // уровень затемнения фона водяного знака
            $text = $watermark_options['text'] ? $watermark_options['text'] : "Set some text";
            $dark = imagecolorallocatealpha($new_img, 0, 0, 0, $darkness);
            $white = imagecolorallocate($new_img, 255, 255, 255);
            imagefilledrectangle($new_img, 0, $imageheight - 20, 125, $imageheight - 2, $dark);
            imagettftext($new_img, 13, 0, 10, $imageheight - 5, $white, $font_file, $text);

            $write_func($new_img, $file_path, $image_quality);
            imagedestroy($new_img);
            return true;
        } catch (Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    protected function get_absolute_path($path)
    {
        $path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
        $parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
        $absolutes = array();
        foreach ($parts as $part) {
            if ('.' == $part) continue;
            if ('..' == $part) {
                array_pop($absolutes);
            } else {
                $absolutes[] = $part;
            }
        }
        return implode(DIRECTORY_SEPARATOR, $absolutes);
    }

    protected function save_descriptions_info($print_response)
    {
        $descriptionsInfo = $this->get_post_param('descriptions_info');
        if (!$descriptionsInfo) {
            $this->http_response(400, json_encode(array('message' => 'descriptions_info is absent'), 'text/json'));
            return '';
        }
        $descriptionsInfo = json_decode($descriptionsInfo, true);
        $response = array();
        foreach ($descriptionsInfo as $item) {
            $file = new stdClass();
            $file->name = $item['fileName'];
            $file->title = $item['title'];
            $file->url = $this->get_download_url($file->name);
            $this->update_file_json($file);
        }
        return $this->generate_response($response, $print_response);
    }

    protected function get_file_object($file_name)
    {
        if ($this->is_json_file($file_name)) {
            // json файлы игнорируем при чтении каталога
            return null;
        }
        $file = parent::get_file_object($file_name);
        if (!$file) {
            return $file;
        }
        $metadata = $this->read_file_json($file);
        if ($metadata) {
            if ($metadata['description']) {
                $file->title = $metadata['description'];
            }
        }
        return $file;
    }

    protected function is_json_file($file_path)
    {
        if (!preg_match('/\.(json)$/i', $file_path)) {
            return false;
        }
        return true;
    }

}
