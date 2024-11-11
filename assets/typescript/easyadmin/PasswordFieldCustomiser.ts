class PasswordFieldCustomiser {
  public customiseFieldsLayout(): void {
    const passwordFields: NodeListOf<HTMLDivElement> =
      document.querySelectorAll<HTMLDivElement>(".field-password");

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

document.addEventListener("DOMContentLoaded", () => {
  new PasswordFieldCustomiser().customiseFieldsLayout();
});
