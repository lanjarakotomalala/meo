export class CheckoutSteps {
    constructor()
    {
        this.checkoutSteps = document.querySelector('#checkout-op-steps');
        if (!this.checkoutSteps) {
              return;
        }
        this.stepToggles = this.checkoutSteps.querySelectorAll('.step .head');
        this.nextButtons = document.querySelectorAll('.next-step');
        this.stepMapping = {
            1: 'login',
            2: 'billing',
            3: 'shipping',
            4: 'payment'
        };

        this.stickySubmit = document.getElementById('sticky_submit');
    }

    initialize()
    {
        if (!this.stepToggles || !this.nextButtons) {
            return;
        }

        this.checkoutSteps.addEventListener('click', (event) => {
            const target = event.target;
            if (target.classList.contains('title')) {
                this.handleStepToggle(target);
            } else if (target.classList.contains('next-step')) {
                this.handleNextStep(target);
            }
        });

        if (this.stickySubmit) {
            this.stickySubmit.addEventListener('click', this.handleStickySubmit.bind(this));
        }
    }

    handleStepToggle(title)
    {
        const currentStep = title.closest('.step');
        const stepContent = currentStep.querySelector('.step-content');
        const isOpen = currentStep.getAttribute('data-toggle') === '1';
        const isAllowed = currentStep.getAttribute('data-next-allowed') === '1';

        if (!isAllowed) {
            return;
        }

        this.hideAllStepContent(currentStep);

        currentStep.setAttribute('data-toggle', isOpen ? '0' : '1');
        if (isOpen) {
            this.hideStepContent(stepContent);
            document.querySelector('.addressShortInfo').style.display = 'flex';
            document.querySelector('.shippingInfo').style.display = 'flex';
        } else {
            this.showStepContent(stepContent);
            this.scrollToCurrentStep(currentStep);
            document.querySelector('.addressShortInfo').style.display = 'none';
            document.querySelector('.shippingInfo').style.display = 'none';
        }
    }

    handleStickySubmit(event)
    {
        event.preventDefault();
        this.paymentSubmit = document.querySelector('button#place_order');
        if (this.paymentSubmit) {
            this.paymentSubmit.removeAttribute('disabled');
            this.paymentSubmit.click();
        }
    }

    handleNextStep(button)
    {
        const currentStep = button.closest('.step');
        const dataStep = parseInt(button.dataset.currentStep);
        const nextStepNumber = dataStep + 1;
        const nextStepKey = this.stepMapping[nextStepNumber];
        const nextStep = this.checkoutSteps.querySelector(`.step[data-step="${nextStepKey}"]`);

        if (dataStep === 2) {
            let firstName = document.getElementById('billing_first_name').value;
            let lastName = document.getElementById('billing_last_name').value;
            let address = document.getElementById('billing_address_1').value;
            let postcode = document.getElementById('billing_postcode').value;
            let city = document.getElementById('billing_city').value;

            let addressShortInfoDiv = document.querySelector('.addressShortInfo');
            addressShortInfoDiv.style.display = 'flex';
            let pElement = addressShortInfoDiv.querySelector('p');

            if (firstName && lastName && address && postcode && city) {
                let fullAddress = firstName + " " + lastName + " - " + address + " " + postcode + " " + city;
                pElement.textContent = fullAddress;
            } else {
                pElement.textContent = "Veuillez remplir tous les champs";
            }
        }

        if (dataStep === 3) {
            let shippingMethods = document.querySelectorAll('#shipping_method li input');
            let addressShortInfoDiv = document.querySelector('.shippingInfo');
            addressShortInfoDiv.style.display = 'flex';
            let pElement = addressShortInfoDiv.querySelector('p');

            shippingMethods.forEach(function (input) {
                if (input.checked) {
                    var label = input.closest('li').querySelector('label').textContent.trim();

                    if (label) {
                        pElement.textContent = label;
                    }
                }
            });
        }

        if (currentStep && nextStep) {
            this.hideAllStepContent(currentStep);
            currentStep.setAttribute('data-toggle', '0');
            nextStep.setAttribute('data-toggle', '1');
            this.hideStepContent(currentStep.querySelector('.step-content'));
            this.showStepContent(nextStep.querySelector('.step-content'));
            this.scrollToCurrentStep(nextStep);
        }
    }


    hideAllStepContent(currentStep)
    {
        const stepContents = this.checkoutSteps.querySelectorAll('.step-content');

        stepContents.forEach((content) => {
            const isCurrentStep = content.closest('.step') === currentStep;
            content.closest('.step').setAttribute('data-toggle', isCurrentStep ? '1' : '0');
            this.hideStepContent(content);
        });
    }

    hideStepContent(stepContent)
    {
        Object.assign(stepContent.style, {
            maxHeight: '0',
            opacity: '0',
            transition: 'max-height 0.3s ease, opacity 0.3s ease'
        });
    }

    showStepContent(stepContent)
    {
        Object.assign(stepContent.style, {
            maxHeight: '100%',
            opacity: '1',
            transition: 'max-height 0.3s ease, opacity 0.3s ease'
        });
    }
    scrollToCurrentStep(currentStep)
    {
        const previousStep = currentStep.previousElementSibling;
        if (!previousStep) {
            return;
        }

        window.scrollTo({
            top: previousStep.offsetTop,
            behavior: 'smooth'
        });
    }
}

const checkoutSteps = new CheckoutSteps();
checkoutSteps.initialize();
