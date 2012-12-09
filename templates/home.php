<!DOCTYPE html>
<html lang='en'>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
        <meta charset="utf-8"/>
        <title>IMA: Image Manipulation Service</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
        <meta name="description" content="Alter images via simple URL routes."/>
        <meta name="author" content="Jim"/>

        <!-- Le styles -->
        <link href="<?=\Photog\configured_path('base_url', 'css/bootstrap.min.css');?>" media="all" type="text/css" rel="stylesheet">
        <link href="<?=\Photog\configured_path('base_url', 'css/font-awesome.css');?>" media="all" type="text/css" rel="stylesheet">
        <link href="<?=\Photog\configured_path('base_url', 'css/style.css');?>" media="all" type="text/css" rel="stylesheet">

        <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
        <!--[if lt IE 9]>
        <script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script>
        <![endif]-->
        <!-- Le JQuery, for easy JavaScript -->
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
        <script type="text/javascript">
            BASE_URL = '<?=\Photog\Config::main('base_url');?>';
        </script>
    </head>
    <body>
        <header class="navbar">
            <div class="navbar-inner">
                <div class="container">
                    <ul class="nav pull-right">
                        <li><a href='#'><i class="icon-github"></i> GitHub</a></li>
                    </ul>
                </div>
            </div>
        </header>
        <div class="container">
            <div class="hero-unit">
                <h1><i class="icon-cogs"></i> IMA: Image Manipulation</h1>
                <p>Alter web images with simple URL routes. All you need to do is specify the source image.</p>
                <form id="ima-form" class="well">
                    <h3>Try it out</h3>
                    <select name="operation" required="true">
                        <option value="">-Operation-</option>
                        <option value="resize">/resize</option>
                        <option value="rotate">/rotate</option>
                        <option value="crop">/crop</option>
                        <option value="filter">/filter</option>
                    </select>
                    <div id="resize" class="hide">
                        <strong>/</strong>
                        <input name="resize" type="text" placeholder="Resize Dimensions WxH"/>
                    </div>
                    <div id="rotate" class="hide">
                        <strong>/</strong>
                        <input name="rotate" type="text" placeholder="Rotation Amount"/>
                    </div>
                    <div id="crop" class="hide">
                        <strong>/</strong>
                        <input name="crop-tl" type="text" placeholder="Top Left Corner X,Y"/>
                        <strong>/</strong>
                        <input name="crop-br" type="text" placeholder="Bottom Right Corner X,Y"/>
                    </div>
                    <div id="filter" class="hide">
                        <select name="filter">
                            <option value="blur">/blur</option>
                            <option value="charcoal">/charcoal</option>
                            <option value="emboss">/emboss</option>
                            <option value="negate">/negate</option>
                            <option value="sharpen">/sharpen</option>
                        </select>
                        <strong>/</strong>
                        <input name="extra" type="text" placeholder="Extra Params P1,P2"/>
                    </div>
                    <strong>?src=</strong>
                    <input name="src" type="text" required="true" placeholder="Image URL"/>
                    <br/>
                    <input type="submit" class="btn btn-success btn-large" value="Submit"/>
                </form>
            </div>
            <div class="row">
                <div class="span3">
                    <h2>Resize <i class="icon-resize-small"></i></h2>
                    <p>Use this route to resize an image:</p>
                    <p><abbr title="Ex. <?=\Photog\Config::main('base_url');?>/resize/80x50?src=...">/resize/&lt;width&gt;x&lt;height&gt;</abbr></p>
                    <p>Where width and height are the pixel dimensions you wish to resize an image to. You also have the option to specify a few dimension aliases like 'thumbnail' and 'default':</p>
                    <p><abbr title="Ex. <?=\Photog\Config::main('base_url');?>/resize/thumbnail?src=...">/resize/thumbnail</abbr> <small class="muted">Dimensions: 80x</small></p>
                    <p><abbr title="Ex. <?=\Photog\Config::main('base_url');?>/resize/default?src=...">/resize/default</abbr> <small class="muted">Dimensions: 640x</small></p>
                    <p>Finally if you want to uniformly scale an image based only on width or height you can optionally leave one dimension blank:</p>
                    <p><abbr title="Ex. <?=\Photog\Config::main('base_url');?>/resize/80x?src=...">/resize/&lt;width&gt;x</abbr></p>
                    <p><abbr title="Ex. <?=\Photog\Config::main('base_url');?>/resize/x50?src=...">/resize/x&lt;height&gt;</abbr></p>
                    <p><span class="label label-important">Default Value</span> If no dimensions are specified then the 'default' dimension alias is used.</p>
                </div>
                <div class="span3">
                    <h2>Rotate <i class="icon-retweet"></i></h2>
                    <p>Use this route to rotate an image:</p>
                    <p><abbr title="Ex. <?=\Photog\Config::main('base_url');?>/rotate/30?src=...">/rotate/&lt;degrees&gt;</abbr></p>
                    <p>Where degrees are specified as the amount of rotation for the image. Note that you can also specify negative degrees.</p>
                    <p><span class="label label-important">Default Value</span> If no rotation is specified then the default value is '-90'</p>
                </div>
                <div class="span3">
                    <h2>Crop <i class="icon-cut"></i></h2>
                    <p>Use this route to crop an image:</p>
                    <p><abbr title="Ex. <?=\Photog\Config::main('base_url');?>/crop/100,120/200,240?src=...">/crop/&lt;x&gt;,&lt;y&gt;/&lt;x&gt;,&lt;y&gt;</abbr></p>
                    <p>Where the first set of points define the top right corner of the area that will be cropped. The second <strong>optional</strong> set of points represent the bottom left point of the cropped area. If The second point isn't set then the bottom left corner is considered the bottom left corner of the image.</p>
                    <p><span class="label label-important">Default Value</span> If no points are specified then the image will not be cropped.</p>
                </div>
                <div class="span3">
                    <h2>Filter <i class="icon-filter"></i></h2>
                    <p>Use this route to add a filter to an image:</p>
                    <p><abbr title="Ex. <?=\Photog\Config::main('base_url');?>/filter/blur/0,5?src=...">/filter/&lt;type&gt;/&lt;extra params&gt;</abbr></p>
                    <p>Where the type is the type of filter to apply. Currently the filter types are:</p>
                    <ul>
                        <li>Blur <small class="muted">Extra Params: &lt;radius&gt;,&lt;sigma&gt;</small></li>
                        <li>Charcoal <small class="muted">Extra Params: &lt;radius&gt;,&lt;sigma&gt;</small></li>
                        <li>Emboss <small class="muted">Extra Params: &lt;radius&gt;,&lt;sigma&gt;</small></li>
                        <li>Negate <small class="muted">No Extra Params</small></li>
                        <li>Sharpen <small class="muted">Extra Params: &lt;radius&gt;,&lt;sigma&gt;</small></li>
                    </ul>
                    <p><span class="label label-important">Default Value</span> If extra parameters are required but not set the default is 0,1.</p>
                </div>
            </div>
            <hr/>
        </div>
        <footer>
            <div class="container"><p>&copy; <a href="http://jimsaunders.net">Jim Saunders</a> 2012</p></div>
        </footer>

        <script src="<?=\Photog\configured_path('base_url', 'js/bootstrap.min.js');?>"></script>
        <script src="<?=\Photog\configured_path('base_url', 'js/page.js');?>"></script>
    </body>
</html>