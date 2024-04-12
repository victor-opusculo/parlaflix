
Parlaflix ??= {};
Parlaflix.Helpers ??= {};

Parlaflix.Helpers.URLGenerator =
{
    generatePageUrl(pagePath, query = {})
    {
        const qs = Object.keys(query).length > 0 ? (useFriendlyUrls ? '?' : '&') + this.generateQueryString(query) : '';
        if (useFriendlyUrls)
            return baseUrl + (pagePath[0] === '/' ? pagePath + qs : '/' + pagePath + qs);
        else
            return baseUrl + `/index.php?page=${pagePath}${qs}`;
    },

    generateFileUrl(filePathFromRoot, query = {})
    {
        const qs = Object.keys(query).length > 0 ? '?' + this.generateQueryString(query) : '';
        if (useFriendlyUrls)
            return baseUrl + `/--file/${filePathFromRoot}${qs}`;
        else
            return baseUrl + (filePathFromRoot[0] === '/' ? '/' + filePathFromRoot.substring(1) + qs : '/' + filePathFromRoot + qs);
    },

    generateScriptUrl(filePathFromScriptDir, query = {})
    {
        const qs = Object.keys(query).length > 0 ? '?' + this.generateQueryString(query) : '';
        if (useFriendlyUrls)
            return baseUrl + `/--script/${filePathFromScriptDir}${qs}`;
        else
            return baseUrl + (filePathFromScriptDir[0] === '/' ? '/script' + filePathFromScriptDir + qs : '/script/' + filePathFromScriptDir + qs);
    },

    generateApiUrl(routePath, query = {})
    {
        const qs = Object.keys(query).length > 0 ? (useFriendlyUrls ? '?' : '&') + this.generateQueryString(query) : '';
        if (useFriendlyUrls)
            return baseUrl + '/--api' + (routePath[0] === '/' ? routePath + qs : '/' + routePath + qs);
        else
            return baseUrl + `/api.php?route=${routePath}${qs}`;
    },

    generateQueryString(queryData)
    {
        return Object.entries(queryData).reduce( (prev, [ currKey, currVal ]) => (prev ? prev + '&' : '') + `${currKey}=${encodeURI(currVal)}`, '');
    },

    goToPageOnSuccess(page, query = {})
    {
        return (([ msgBoxReturnValue, jsonResult ]) => 
        {
            if (msgBoxReturnValue === 'ok' && jsonResult?.success)
                window.location.href = this.generatePageUrl(page, query);
        });
    },

    goToPageOrBackToOnSuccess(page, query = {})
    {
        return (([ msgBoxReturnValue, jsonResult ]) => 
        {
            if (msgBoxReturnValue === 'ok' && jsonResult?.success)
            {
                const url = new URL(window.location.href);
                if (url.searchParams.has('back_to'))
                    window.location.href = this.generatePageUrl(url.searchParams.get('back_to') ?? '/', query)
                else
                    window.location.href = this.generatePageUrl(page, query);
            }
        });
    }
};