/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'Magento_Checkout/js/view/payment/default',
        'jquery'
    ],
    function (Component,  $) {
        'use strict'
        return Component.extend({
            defaults: {
                template: 'Magento_NovaTwoPay/payment/form',
                transactionResult: ''
            },

            initObservable: function () {

                this._super()
                    .observe([
                        'transactionResult'
                    ]);
                return this;
            },

            getCode: function() {
                return 'nova_two_pay';
            },

            getData: function() {
                return {
                    'method': this.item.method,
                    'additional_data': {
                        'card_num': document.getElementById("nova-apiplus-card-number").value,
                        'card_expiry': document.getElementById("nova-apiplus-card-expiry").value,
                        'card_cvc': document.getElementById("nova-apiplus-card-cvc").value,
                    }
                };
            },

            setTransactionResult: function (transactionResult) {
                this.transactionResult = transactionResult;
            },




            userSubmit: function (data) {

                // 获取参数
                var card_num = document.getElementById("nova-apiplus-card-number").value;
                var card_expiry = document.getElementById('nova-apiplus-card-expiry').value;
                var card_cvc = document.getElementById('nova-apiplus-card-cvc').value;


                var username = document.getElementsByName('username')[0].value;
                var firstname = document.getElementsByName('firstname')[0].value;
                var lastname = document.getElementsByName('lastname')[0].value;
                var street = document.getElementsByName('street[0]')[0].value;
                var city = document.getElementsByName('city')[0].value;
                var region_id = document.getElementsByName('region_id')[0].value;
                var postcode = document.getElementsByName('postcode')[0].value;
                var telephone = document.getElementsByName('telephone')[0].value;

                // 创建要发送的数据对象
                var data = {
                    'username': username,
                    'firstname': firstname,
                    'lastname': lastname,
                    'street': street,
                    'city': city,
                    'region_id': region_id,
                    'postcode': postcode,
                    'telephone': telephone,
                    'card_num': card_num,
                    'card_expiry': card_expiry,
                    'card_cvc': card_cvc
                };

                var self = this; // 将this存储在self变量中
                // 发送AJAX请求
                $.ajax({
                    type: 'POST',
                    url: "/nova2pay/payment/novapayment",
                    // url: "http://nova-magento.com/nova2pay/payment/payment",
                    data: data,
                    success: function (response) {
                        // 处理控制器返回的响应
                        if (response['code'] == 200) {
                            //window.location.href = response['url'];
                            self.placeOrder();
                            setTimeout(function() {
                                window.location.href = response['url'];
                            }, 300); // 将秒数转换为毫秒
                            //this.redirectTo(response['url'], 2);
                        }
                        //this.redirectTo('success', 5)
                        console.log(response);
                    },
                    error: function(xhr, status, error) {
                        // 处理错误
                        console.error(xhr.responseText);
                    }
                });
            },

                // 跳转函数
            redirectTo: function(url, delayInSeconds) {
                setTimeout(function() {
                    window.location.href = url;
                }, delayInSeconds * 1000); // 将秒数转换为毫秒
            },


            beforePlaceOrder: function (data) {
                this.setTransactionResult(data.nonce);
                this.placeOrder();
            },

            getTransactionResults: function() {
                return _.map(window.checkoutConfig.payment.nova_two_pay.transactionResults, function(value, key) {
                    return {
                        'value': key,
                        'transaction_result': value
                    }
                });
            }
        });
    }
);