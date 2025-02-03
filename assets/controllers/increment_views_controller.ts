// @ts-ignore
import { Controller } from "@hotwired/stimulus";

/* stimulusFetch: 'lazy' */
export default class extends Controller {
  async connect(): Promise<void> {
    const isDefaultLocale: boolean = document.documentElement.lang === "fr";
    const incrementViewsPath: string = isDefaultLocale
      ? "/incrementer-nombre-de-vues"
      : "/increment-number-of-views";
    const incrementViewsUrl: string =
      window.location.pathname + incrementViewsPath;

    try {
      await fetch(incrementViewsUrl, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
      });
    } catch (error) {
      console.error("Error while incrementing view: ", error);
    }
  }
}
