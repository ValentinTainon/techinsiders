// @ts-ignore
import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  element: HTMLElement;
  connect() {
    this.element.textContent =
      "Hello Stimulus! Edit me in assets/controllers/hello_controller.ts";
  }
}
