
window.onload = () => {
    const formAjax = document.querySelector("#formAjax");

    document.querySelector("#buttonAjax").addEventListener("click", () => {

            const Form = new FormData(formAjax);
            const Params = new URLSearchParams();

            Form.forEach((value, key) => {
                Params.append(key, value);
            });

            const Url = new URL(window.location.href);

            fetch(Url.pathname + "?" + Params.toString() + "&ajax=1", {
                headers: {
                    "X-Requested-With": "XMLHttpRequest"
                }
            }).then(response =>
                response.json()
            ).then(data => {

                const content = document.querySelector("#contentCommentaire");
                content.innerHTML = data.content;

                history.pushState({}, null, Url.pathname);
            }).catch(e => alert(e));

        });
}
