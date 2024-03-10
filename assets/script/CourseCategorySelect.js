window.addEventListener('load', function()
{
    const sel = document.getElementById('courseCategorySelect');

    if (sel)
        sel.onchange = e =>
        {
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.delete('page_num');

            if (e.target.value != 0)
                currentUrl.searchParams.set('category_id', e.target.value);
            else
                currentUrl.searchParams.delete('category_id');

            window.location.href = currentUrl.toString();
        };
    
});