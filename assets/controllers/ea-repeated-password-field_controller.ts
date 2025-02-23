// @ts-ignore
import { Controller } from "@hotwired/stimulus";

/* stimulusFetch: 'lazy' */
export default class extends Controller {
  // Password field
  declare element: HTMLDivElement;

  connect(): void {
    const passwordField = this.element;
    const fieldContainer: HTMLElement | null = passwordField.parentElement;

    if (fieldContainer) {
      this.fixFieldLayout(fieldContainer);
    }
  }

  private fixFieldLayout(fieldContainer: HTMLElement): void {
    this.addMissingBootstrapClasses(fieldContainer);
    this.removeUselessRowSeparator(fieldContainer);
  }

  private addMissingBootstrapClasses(fieldContainer: HTMLElement): void {
    fieldContainer.classList.add("col-sm-6", "col-md-5");
  }

  private removeUselessRowSeparator(fieldContainer: HTMLElement): void {
    const nextElement: Element | null = fieldContainer.nextElementSibling;

    if (nextElement && nextElement.classList.contains("flex-fill")) {
      nextElement.remove();
    }
  }
}
