// @ts-ignore
import { Controller } from "@hotwired/stimulus";

/* stimulusFetch: 'lazy' */
export default class extends Controller {
  private isViewIncremented: boolean = false;

  connect(): void {
    if (this.isViewIncremented) return;

    const isDefaultLocale: boolean = document.documentElement.lang === "fr";
    const incrementViewsPath: string = isDefaultLocale
      ? "/incrementer-nombre-de-vues"
      : "/increment-number-of-views";
    const incrementViewsUrl: string =
      window.location.pathname + incrementViewsPath;

    fetch(incrementViewsUrl, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
    })
      .then((response) => {
        if (response.ok) {
          this.isViewIncremented = true;
        }
      })
      .catch((error) => {
        console.error("Error while incrementing view: ", error);
      });
  }
}
