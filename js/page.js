$(function()
{
    $('#ima-form').submit(function(e)
    {
        var $form = $(this);
        var url = BASE_URL;

        e.stopPropagation();
        e.preventDefault();

        $form.find(':visible:input[type!="submit"]').each(function(index, elm)
        {
            if(elm.name === 'src')
            {
                if(elm.value !== '')url += '?src='+elm.value;
                else return false;
            }
            else
            {
                url += '/';
                if(elm.value !== '')url += elm.value;
                else return false;
            }
        });

        if(url.match(/\?src=/) === null)alert('You need to enter a source image.');
        else window.location = url;

    }).find('select[name="operation"]').change(function(e)
    {
        var $select = $(this);
        var val = $select.val();

        $select.parent().find('.hide:visible').hide();
        if(val.length > 0)
            $('#'+val).css('display', 'inline');
    });
});