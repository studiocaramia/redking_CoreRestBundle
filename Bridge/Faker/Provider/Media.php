<?php

namespace Redking\Bundle\CoreRestBundle\Bridge\Faker\Provider;

use Faker\Provider\Base as BaseProvider;
use Faker\Generator;

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Media extends BaseProvider
{
    /**
     * @var Kernel
     */
    private $kernel;

    /**
     * @param \Faker\Generator $generator
     * @param Kernel
     */
    public function __construct(Generator $generator, Kernel $kernel)
    {
        $this->kernel = $kernel;
        parent::__construct($generator);
    }

    /**
     * Retourne une url d'image de lorempixel
     * @param  [type] $width    [description]
     * @param  [type] $height   [description]
     * @param  string $category [description]
     * @return [type]           [description]
     */
    public function imageUrl($width, $height, $category = '')
    {
        $url = 'http://lorempixel.com/'.$width.'/'.$height.'/';
        
        if ($category !== '') {
            $url .= $category.'/';
        }

        return $url;
    }

    /**
     * Retourne une url d'image avec un timestamp en suffix
     * @param  [type] $width    [description]
     * @param  [type] $height   [description]
     * @param  string $category [description]
     * @return [type]           [description]
     */
    public function imageUrlTimestamp($width, $height, $category = '')
    {
        return $this->imageUrl($width, $height, $category).'?'.microtime();
    }

    /**
     * Retourne une video youtube aléatoire
     * @return [type] [description]
     */
    public function youtube()
    {
        // appel du site et recherche dans le code l'id de la vidéo
        $content = file_get_contents('http://randomyoutube.net/watch');

        if (preg_match('/href\=\"http\:\/\/www\.youtube\.com\/watch\?v\=(.{11})\"/', $content, $match)) {
            $youtube_id = $match[1];
        } 
        // Si pas de trouvé, on en génère un bidon
        else {
            $youtube_id = preg_replace('/([ ])/e',
        'substr( "0123456789-_abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ",     mt_rand(0,62), 1 )', '           ');
        }
        
        return 'https://www.youtube.com/watch?v='.$youtube_id;
    }

    /**
     * Returns an UploadedFile based on a source file
     * @param  string $source 
     * @return UploadedFile
     */
    public function uploadfile($source)
    {
        $source = $this->kernel->locateResource($source);
        return new UploadedFile($source, basename($source));
    }

    /**
     * Returns an UploadedFile based on a random url
     * @param  [type] $width    [description]
     * @param  [type] $height   [description]
     * @param  string $category [description]
     * @return [type]           [description]
     */
    public function uploadRandomImage($width, $height, $category = '')
    {
        $url = $this->imageUrl($width, $height, $category);
        $image_content = file_get_contents($url);

        // Sauvegarde du fichier temporaire
        $tmp_file = tempnam('/tmp', "rdm_img");
        $fh = fopen($tmp_file, 'w+');
        fwrite($fh, $image_content);
        fclose($fh);

        return new UploadedFile($tmp_file, basename($tmp_file));
    }
}
