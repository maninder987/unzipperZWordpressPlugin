<?php
/*

Plugin Name: Unzipper Z
Description: Unzips zip files
Author: Manu
Version:1.0
Author URI: https://zarx.biz

*/

class UnzipperZ {

    public function __construct()
    {
        // Hook init function.
        add_action('admin_menu', array($this, 'wz_unzipper_z_init'));
    
        // Add bootstrap for admin.    
        wp_enqueue_style('datatablecss','//cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' );
    }

    // Add option in admin sidebar

    public function wz_unzipper_z_init()
    {
        add_menu_page( 
            'UnZipper Z', 
            'Upload media zip', 
            'manage_options',
            'unzipper_z_zarx',
            array($this, 'unzipper_z_zarx'),
            'dashicons-media-default',
            35 );
    }

    public function allowed_file_type($filetype)
    {
        $allowed_file_type = array('image/png', 'image/jpeg', 'image/jpg', 'image/gif');
        
        if(in_array($filetype, $allowed_file_type))
        {
            return true;
        }

        return false;
    }

    public function unzipper_z_zarx()
    {
        echo '<div class="container m-5">
                <div class="jumbotron">
                    <h1 class="display-4">Unzipper Z</h1>
                    <p class="lead">
                        Unzip file and add media files.
                    </p>
                    <hr class="my-4">
                    <p>It uses utility classes for typography and spacing to space content out within the larger container.</p>
                    <p class="lead">
                    <a class="btn btn-primary btn-lg" href="#" role="button">Learn more</a>
                    </p>
                </div>
            <hr>
            <h3 class="lead">Upload ZipFile</h3>';

            if(isset($_FILES['fileToUpload']))
            {
                // Gives ../wp-content/uploads/2022/02
                $dir = "../wp-content/uploads". wp_upload_dir()['subdir'];
                
                // Upoad file
                $target_file = $dir.'/'.basename($_FILES['fileToUpload']['name']);
                move_uploaded_file($_FILES['fileToUpload']['tmp_name'], $target_file);
                $file_name = basename($_FILES['fileToUpload']['name']);
                
                // Get zip class
                $zip = new ZipArchive;

                $res = $zip->open($target_file);

                if($res == TRUE)
                {
                    $zip->extractTo($dir);
                    //echo "File ".$file_name."unzipped, ".
                    wp_upload_dir()['url'];

                    ///echo 'There are '.$zip->numFiles;

                    for($i = 0; $i < $zip->numFiles; $i++)
                    {
                        $media_file_name = wp_upload_dir()['url'].'/'.$zip->getNameIndex($i);
                        $file_type = wp_check_filetype( basename($media_file_name), null);

                        $allowed = $this->allowed_file_type($file_type['type']);
                        
                        if(strpos($zip->getNameIndex($i), 'MACOSX')){
                            continue;
                        }
                        
                        if($allowed)
                        {
                            echo '<a href="'.$media_file_name.'" target="_blank">'.$media_file_name.'</a> Type:'.$file_type['type'];
                        
                            // Adding to media library.

                            $attachment = array(
                                'guid' => $media_file_name,
                                'post_mime_type' => $file_type['type'],
                                'post_title' => $zip->getNameIndex($i),
                                'post_content' => '',
                                'post_status' => 'inherit',
                            );

                            // Insert the attachment.
                            
                            $attach_id = wp_insert_attachment( $attachment, $dir.'/'.$zip->getNameIndex($i));
                            
                            $attach_data = wp_generate_attachment_metadata( $attach_id, $dir.'/'. $zip->getNameIndex($i));

                            wp_update_attachment_metadata($attach_id, $attach_data);
                        }
                        else
                        {
                            echo $zip->getNameIndex($i). ' could not be uploaded';
                        }
                    }
                    
                }
                else
                {
                    echo 'Not successfully uploaded';
                }
                    
                $zip->close();
                exit;
            }

            echo ('
                <form class="form mb-5" 
                enctype="multipart/form-data"
                method="post" 
                action="/wp-admin/admin.php?page=unzipper_z_zarx">
                    <div class="form-group mb-5">
                        <label>Select Zip File</label>   
                        <input type="file" class="form-control" name="fileToUpload" id="fileToUpload">
                    </div>
                    <div class="form-group">
                      <input type="submit" class="btn btn-info" value="File to upload">
                    </div>
                </form>
            ');

            echo '</div>'; // End container
    }
}

// Instantiate the class.

new UnzipperZ();














?>