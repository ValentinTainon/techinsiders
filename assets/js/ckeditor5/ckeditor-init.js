const minCharacters = 500;
const container = document.querySelector('.ck-update');
const progressCircle = document.querySelector('.ck-update__chart__circle');
const charactersBox = document.querySelector('.ck-update__chart__characters');
const wordsCountBox = document.querySelector('.ck-update__words .count');
const circleCircumference = Math.floor(2 * Math.PI * progressCircle.getAttribute('r') );
const submitButtons = document.querySelectorAll('button[type="submit"]');

ClassicEditor.Editor
    .create(document.querySelector('#editor'), {
        // language: document.documentElement.getAttribute('lang'),
        wordCount: {
            onUpdate: stats => {
                const charactersProgress = stats.characters / minCharacters * circleCircumference;
                const isLimitReached = stats.characters >= minCharacters;
                const isCloseToLimit = !isLimitReached && stats.characters > minCharacters * .8;
                const circleDashArray = Math.min(charactersProgress, circleCircumference);

                progressCircle.setAttribute('stroke-dasharray', `${circleDashArray},${circleCircumference}`);

                if (!isLimitReached) {
                    charactersBox.textContent = `${ stats.characters - minCharacters}`;
                } else {
                    charactersBox.textContent = stats.characters;
                }

                wordsCountBox.textContent = stats.words;

                container.classList.toggle('ck-update__limit-close', isCloseToLimit);

                container.classList.toggle('ck-update__limit-not-reached', !isLimitReached);

                submitButtons.forEach(button => {
                    button.toggleAttribute('disabled', !isLimitReached);
                })
            }
        }
    })
    .then( editor => {
        console.log('Editor was initialized', editor);
    })
    .catch(error => {
        console.error(error);
    });