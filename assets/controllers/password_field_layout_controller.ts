// @ts-ignore
import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  connect(): void {
    const passwordFields: NodeListOf<HTMLDivElement> =
      document.querySelectorAll<HTMLDivElement>(
        "form[name=User] div.field-password"
      );

    if (passwordFields.length > 0) {
      this.customiseFieldsLayout(passwordFields);
    }
  }

  private customiseFieldsLayout(
    passwordFields: NodeListOf<HTMLDivElement>
  ): void {
    passwordFields.forEach((field) => {
      const fieldContainer: HTMLElement | null = field.parentElement;

      if (fieldContainer) {
        this.addBootstrapClasses(fieldContainer);
        this.removeUselessRowSeparator(fieldContainer);
      }
    });
  }

  private addBootstrapClasses(fieldContainer: HTMLElement): void {
    fieldContainer.classList.add("col-sm-6", "col-md-5");
  }

  private removeUselessRowSeparator(fieldContainer: HTMLElement): void {
    const nextElement: Element | null = fieldContainer.nextElementSibling;

    if (nextElement && nextElement.classList.contains("flex-fill")) {
      nextElement.remove();
    }
  }
}
