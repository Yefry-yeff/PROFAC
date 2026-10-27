            .proveedores-view .modal {
                z-index: 5000 !important;
            }

            body.proveedor-modal-open .modal-backdrop {
                z-index: 4990 !important;
            }

            .proveedores-view .modal-dialog {
                width: calc(100% - 32px);
                max-width: 860px;
                margin: 24px auto;
            }

            .proveedores-view .modal-content {
                display: flex;
                flex-direction: column;
                max-height: calc(100vh - 48px);
                overflow: hidden;
                border: 0;
                border-radius: 12px;
                box-shadow: 0 18px 50px rgba(35, 50, 60, .2);
            }

            .proveedores-view .modal-header {
                flex-shrink: 0;
                padding: 16px 22px;
                border-bottom: 0;
                background: linear-gradient(135deg, #f39c12 0%, #e05a00 100%);
            }

            .proveedores-view .modal-header .modal-title,
            .proveedores-view .modal-header .close {
                color: #fff !important;
            }

            .proveedores-view .modal-header .modal-title {
                font-weight: 600;
            }

            .proveedores-view .modal-header .close {
                opacity: .9;
                text-shadow: none;
            }

            .proveedores-view .modal-body {
                padding: 22px;
                overflow-y: auto;
                background: #fffcf8;
            }

            .proveedores-view .modal-body label {
                color: #6d4c1e;
                font-size: 12px;
                font-weight: 600;
            }

            .proveedores-view .modal-body .form-control {
                min-height: 38px;
                border: 1px solid var(--proveedores-border);
                border-radius: 6px;
                box-shadow: none;
            }

            .proveedores-view .modal-body .form-control:focus {
                border-color: var(--proveedores-orange);
                box-shadow: 0 0 0 3px rgba(230, 126, 34, .2);
            }

            .proveedores-view .proveedor-form-intro {
                margin-bottom: 20px;
                color: var(--proveedores-muted);
                font-size: 13px;
            }

            .proveedores-view .proveedor-section {
                min-width: 0;
                padding: 18px 20px 4px;
                margin: 0 0 20px;
                border: 1px solid #efdfcc;
                border-radius: 10px;
                background: #fff;
            }

            .proveedores-view .proveedor-section legend {
                display: flex;
                align-items: center;
                gap: 10px;
                width: auto;
                padding: 0 8px;
                margin: 0;
                color: #7d3f00;
                font-size: 15px;
                font-weight: 700;
            }

            .proveedores-view .proveedor-section-number {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 30px;
                height: 30px;
                border-radius: 9px;
                background: #fdf4e7;
                color: #c45a00;
                font-size: 12px;
            }

            .proveedores-view .proveedor-optional {
                margin-left: 4px;
                color: #939393;
                font-size: 10px;
                font-weight: 400;
            }

            .proveedores-view .proveedor-field-heading {
                display: flex;
                align-items: center;
                justify-content: space-between;
                flex-wrap: wrap;
                gap: 4px 10px;
                margin-bottom: 6px;
            }

            .proveedores-view .proveedor-field-heading label {
                margin: 0;
            }

            .proveedores-view .proveedor-no-registro {
                display: inline-flex;
                align-items: center;
                gap: 5px;
                cursor: pointer;
                white-space: nowrap;
            }

            .proveedores-view .proveedor-no-registro input {
                accent-color: var(--proveedores-orange);
            }

            .proveedores-view .proveedor-no-registro input:checked {
                background-color: var(--proveedores-orange);
                border-color: var(--proveedores-orange);
                color: var(--proveedores-orange);
            }

            .proveedores-view .proveedor-no-registro input:focus {
                --tw-ring-color: rgba(230, 126, 34, .3);
            }

            .proveedores-view .form-control:disabled {
                background: #f5f2ed;
                color: #777;
            }

            .proveedores-view .modal-footer {
                flex-shrink: 0;
                padding: 14px 22px;
                border-top: 1px solid #efdfcc;
                background: #fff;
            }

            .proveedores-view .modal-footer .btn-primary {
                border: 0;
                background: linear-gradient(135deg, #f39c12, #e05a00);
                color: #fff;
            }

            .proveedores-view .modal-footer .btn-primary:hover {
                background: linear-gradient(135deg, #e08e0b, #c04e00);
            }
