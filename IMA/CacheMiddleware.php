<?php namespace IMA;

class CacheMiddleware extends \Slim\Middleware
{
    public function call()
    {
        $app = $this->app;
        $req = $app->request();

        if(array_key_exists('src', $_GET) && !empty($_GET['src']))
        {
            $path = \Photog\configured_path('processed_cache_directory', md5($req->getPath().$_GET['src']));

            if(file_exists($path))
            {
                $res = $app->response();
                $meta = getimagesize($path);
                switch($meta[2])
                {
                    case IMAGETYPE_JPEG:
                        $res['Content-Type'] = 'image/jpeg';
                        break;
                    case IMAGETYPE_GIF:
                        $res['Content-Type'] = 'image/gif';
                        break;
                    case IMAGETYPE_PNG:
                        $res['Content-Type'] = 'image/png';
                        break;
                }
                $res->body(file_get_contents($path));
                return;
            }
        }
        $this->next->call();
    }
}
