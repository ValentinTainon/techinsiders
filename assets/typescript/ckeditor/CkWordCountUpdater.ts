export default class CkWordCountUpdater {

    public static updateStats(stats: { characters: number; words: number }): void {
        const minCharactersLimit: number = 500;
        const container: HTMLDivElement | null = document.querySelector<HTMLDivElement>('.ck-update');
        const progressCircle: SVGCircleElement | null = document.querySelector<SVGCircleElement>('.ck-update__chart__circle');
        const charactersBox: SVGTextElement | null = document.querySelector<SVGTextElement>('.ck-update__chart__characters');
        const wordsCountBox: HTMLSpanElement | null = document.querySelector<HTMLSpanElement>('.ck-update__words .count');
        const submitButtons: NodeListOf<HTMLButtonElement> = document.querySelectorAll<HTMLButtonElement>('button[type="submit"]');

        const circleRadius: number = progressCircle ? parseInt(progressCircle.getAttribute('r') || '0') : 0;
        const circleCircumference: number = Math.floor(2 * Math.PI * circleRadius);
        const charactersProgress: number = (stats.characters / minCharactersLimit) * circleCircumference;
        const circleDashArray: number = Math.min(charactersProgress, circleCircumference);
        const isLimitReached: boolean = stats.characters >= minCharactersLimit;
        const isCloseToLimit: boolean = !isLimitReached && stats.characters > minCharactersLimit * 0.8;

        if (progressCircle) {
            progressCircle.setAttribute('stroke-dasharray', `${circleDashArray},${circleCircumference}`);
        }

        if (charactersBox) {
            charactersBox.textContent = !isLimitReached ? `${stats.characters - minCharactersLimit}` : `${stats.characters}`;
        }

        if (wordsCountBox) {
            wordsCountBox.textContent = `${stats.words}`;
        }

        if (container) {
            container.classList.toggle('ck-update__limit-not-reached', !isLimitReached);
            container.classList.toggle('ck-update__limit-close', isCloseToLimit);
        }

        submitButtons.forEach(button => {
            button.toggleAttribute('disabled', !isLimitReached);
        });
    }
}