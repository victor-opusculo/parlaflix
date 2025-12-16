
Parlaflix.Alerts ??= 
{
    types: 
    {
        error: 'error',
        info: 'info',
        success: 'success'
    },

    prepareButton(...btnValue)
    {
        const ok = document.querySelector("#messageBox button[value='ok']");
        const cancel = document.querySelector("#messageBox button[value='cancel']");
        const yes = document.querySelector("#messageBox button[value='yes']");
        const no = document.querySelector("#messageBox button[value='no']");

        const btns = [ ok, cancel, yes, no ];
        
        for (const btn of btns)
        {
            if (btnValue.includes(btn.value))
            {
                btn.className = 'btn';
                btn.focus();
            }
            else
                btn.className = 'hidden btn';
        }
    },

    push(type, message)
    {
        return new Promise(resolve =>
        {
            const title = document.getElementById('messageBox_title');
            const messageEl = document.getElementById('messageBox_message');
            const msgBox = document.getElementById('messageBox');

            switch (type)
            {
                case this.types.error:
                    title.innerText = "Erro";
                    messageEl.innerText = message;
                    this.prepareButton('ok');
                    break;
                case this.types.info: 
                    title.innerText = "Informação";
                    messageEl.innerText = message;
                    this.prepareButton('ok');
                    break;
                case this.types.success: 
                    title.innerText = "Sucesso!";
                    messageEl.innerText = message;
                    this.prepareButton('ok');
                    break;
            }

            msgBox.onclose = ev => resolve(ev.target.returnValue);
            msgBox.showModal();
        });
    },

    pushFromJsonResult(jsonDecoded)
    {
        if (jsonDecoded.error)
            return this.push(this.types.error, jsonDecoded.error).then(ret => [ret, jsonDecoded ]);

        if (jsonDecoded.info)
            return this.push(this.types.info, jsonDecoded.info).then(ret => [ret, jsonDecoded ]);

        if (jsonDecoded.success)
            return this.push(this.types.success, jsonDecoded.success).then(ret => [ret, jsonDecoded ]);
    },

    pushError(alertMessage)
    {
        return err =>
        {
            this.push(this.types.error, alertMessage);
            console.error(err);
        };
    },
};

Parlaflix.Alerts.prepareButton = Parlaflix.Alerts.prepareButton.bind(Parlaflix.Alerts);
Parlaflix.Alerts.pushFromJsonResult = Parlaflix.Alerts.pushFromJsonResult.bind(Parlaflix.Alerts);
Parlaflix.Alerts.pushError = Parlaflix.Alerts.pushError.bind(Parlaflix.Alerts);
Object.freeze(Parlaflix.Alerts.types);