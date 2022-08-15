<?php

/**
 * @wordpress-plugin
 * Plugin Name:             MixPay Gateway for WooCommerce
 * Plugin URI:              https://github.com/MixPayHQ/mixpay-woocommerce-plugin
 * Description:             Cryptocurrency Payment Gateway.
 * Version:                 1.0.0
 * Author:                  MixPay Payment
 * License:                 GPLv2 or later
 * License URI:             http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:             wc-mixpay-gateway
 * Domain Path:             /i18n/languages/
 */

/**
 * Exit if accessed directly.
 */
if (! defined('ABSPATH'))
{
    exit();
}

if (version_compare(PHP_VERSION, '7.1', '>=')) {
    ini_set('precision', 10);
    ini_set('serialize_precision', 10);
}

if (! defined('MIXPAY_FOR_WOOCOMMERCE_PLUGIN_DIR')) {
    define('MIXPAY_FOR_WOOCOMMERCE_PLUGIN_DIR', dirname(__FILE__));
}

if (! defined('MIXPAY_FOR_WOOCOMMERCE_ASSET_URL')) {
    define('MIXPAY_FOR_WOOCOMMERCE_ASSET_URL', plugin_dir_url(__FILE__));
}

if (! defined('MIXPAY_VERSION_PFW')) {
    define('MIXPAY_VERSION_PFW', '1.0.0');
}

if (! defined('MIXPAY_SUPPORT_EMAIL')) {
    define('MIXPAY_SUPPORT_EMAIL', 'bd@mixpay.me');
}

if (! defined('MIXPAY_ICON_URL')) {
    define('MIXPAY_ICON_URL', 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAA6IAAAEKCAYAAAAIKOgtAAAAAXNSR0IArs4c6QAAIABJREFUeF7tnQe0FFXWhfcTzNlR1DGMOuacc8AcUDFHEDCgYsSAYs6KYEIRMwZQMWMadQyYc8Sc8yjmhAl9/9qW7+fRXdVd1V3dXd39nbVczLy+dcN3q7tq33vuOS2KYTNv3jrtJH+oq1q1rqSlWqV5JE3bInWMcTlFIAABCEAAAhCAAAQgAAEIQKDBCLRK4yX90CK9L+lFtei+Pzto1Je3tvxQbKgthQrM1LV1ro7j1V+t6iFpqmKV8TkEIAABCEAAAhCAAAQgAAEINDWBcWrRFeM76tSvR7V8FEUiUojOuklr31bpRElTNzVGBg8BCEAAAhCAAAQgAAEIQAACSQn81CId/fmdLWeFXZgvRDu3TtFpal2iVu2ctCXKQwACEIAABCAAAQhAAAIQgAAE/p9Ai0aM/Um7a3TLL+2pTCxELUKn0r2SVgMdBCAAAQhAAAIQgAAEIAABCEAgBQKPjh2n9dqL0YmEaKcurcPZCU0BM1VAAAIQgAAEIAABCEAAAhCAwAQC3hm9o6Vb2x/+X4j+fSb0TFhBAAIQgAAEIAABCEAAAhCAAATSJtAiHdR2ZvQvIfpXdNzf9RqBidJGTX0QgAAEIAABCEAAAhCAAAQg8DeBn8ZPqkUcTfcvIdqpS+v5atXe4IEABCAAAQhAAAIQgAAEIAABCFSMQIuGjr2jpU/LzJu3TjvJeH1GntCKoaZiCEAAAhCAAAQgAAEIQAACEAgIjPuzo2Zr6dSltZtadRVUIAABCEAAAhCAAAQgAAEIQAACFSfQou4tnTZpHSapZ8UbowEIQAACEIAABCAAAQhAAAIQgIB0uYXoc5KWgQYEIAABCEAAAhCAAAQgAAEIQKAKBJ5vmWWT1q9bpBmr0BhNQAACEIAABCAAAQhAAAIQgECTE2iVvrEQ/b1F6tjkLBg+BCAAAQhAAAIQgAAEIAABCFSBQKs03q65rVVoiyYgAAEIQAACEIAABCAAAQhAAAJ/EUCIciNAAAIQgAAEIAABCEAAAhCAQFUJIESripvGIAABCEAAAhCAAAQgAAEIQAAhyj0AAQhAAAIQgAAEIAABCEAAAlUlgBCtKm4agwAEIAABCEAAAhCAAAQgAAGEKPcABCAAAQhAAAIQgAAEIAABCFSVAEK0qrhpDAIQgAAEIAABCEAAAhCAAAQQotwDEIAABCAAAQhAAAIQgAAEIFBVAgjRquKmMQhAAAIQgAAEIAABCEAAAhBAiHIPQAACEIAABCAAAQhAAAIQgEBVCSBEq4qbxiAAAQhAAAIQgAAEIAABCEAAIco9AAEIQAACEIAABCAAAQhAAAJVJYAQrSpuGoMABCAAAQhAAAIQgAAEIAABhCj3AAQgAAEIQAACEIAABCAAAQhUlQBCtKq4aQwCEIAABCAAAQhAAAIQgAAEEKLcAxCAAAQgAAEIQAACEIAABCBQVQII0aripjEIQAACEIAABCAAAQhAAAIQQIhyD0AAAhCAAAQgAAEIQAACEIBAVQkgRKuKm8YgAAEIQAACEIAABCAAAQhAACHKPQABCEAAAhCAAAQgAAEIQAACVSWAEK0qbhqDAAQgAAEIQAACEIAABCAAAYQo9wAEIAABCEAAAhCAAAQgAAEIVJUAQrSquGkMAhCAAAQgAAEIQAACEIAABBCi3AMQgAAEIAABCEAAAhCAAAQgUFUCCNGq4qYxCEAAAhCAAAQgAAEIQAACEECIcg9AAAIQgAAEIAABCEAAAhCAQFUJIESripvGIAABCEAAAhCAAAQgAAEIQAAhyj0AAQhAAAIQgAAEIAABCEAAAlUlgBCtKm4agwAEIAABCEAAAhCAAAQgAAGEKPcABCAAAQhAAAIQgAAEIAABCFSVAEK0qrhpDAIQgAAEIAABCEAAAhCAAAQQotwDEIAABCAAAQhAAAIQgAAEIFBVAgjRquKmMQhAAAIQgAAEIAABCEAAAhBAiHIPQAACEIAABCAAAQhAAAIQgEBVCSBEq4qbxiAAAQhAAAIQgAAEIAABCEAAIco9AAEIQAACEIAABCAAAQhAAAJVJYAQrSpuGoMABCAAAQhAAAIQgAAEIAABhCj3AAQgAAEIQAACEIAABCAAAQhUlQBCtKq4aQwCEIAABCAAAQhAAAIQgAAEEKLcAxCAAAQgAAEIQAACEIAABCBQVQII0aripjEIQAACEIAABCAAAQhAAAIQQIhyD0AAAhCAAAQgAAEIQAACEIBAVQkgRKuKm8YgAAEIQAACEIAABCAAAQhAACHKPQABCEAAAhCAAAQgAAEIQAACVSWAEK0qbhqDAAQgAAEIQAACEIAABCAAAYQo9wAEIAABCEAAAhCAAAQgAAEIVJUAQrSquGkMAhCAAAQgAAEIQAACEIAABBCi3AMQgAAEIAABCEAAAhCAAAQgUFUCCNGq4qYxCEAAAhCAAAQgAAEIQAACEECIcg9AAAIQgAAEIAABCEAAAhCAQFUJIESripvGIAABCEAAAhCAAAQgAAEIQAAhyj0AAQhAAAIQgAAEIAABCEAAAlUlgBCtKm4agwAEIAABCEAAAhCAAAQgAAGEKPcABCAAAQhAAAIQgAAEIAABCFSVAEK0qrhpDAIQgAAEIAABCEAAAhCAAAQQotwDEIAABCAAAQhAAAIQgAAEIFBVAgjRquKmMQhAAAIQgAAEIAABCEAAAhBAiHIPQAACEIAABCAAAQhAAAIQgEBVCSBEq4qbxiAAAQhAAAIQgAAEIAABCEAAIco9AAEIQAACEIAABCAAAQhAAAJVJYAQrSpuGoMABCAAAQhAAAIQgAAEIAABhCj3AAQgAAEIQAACEIAABCAAAQhUlQBCtKq4aQwCEIAABCAAAQhAAAIQgAAEEKLcA3VBoEXSLDNKc84izdlJmqOT1GlGaYZppOmnmfCv//cUk0kdJ5E6dJA6dvj730mkP1ul38ZLv//+97/jg39/+ln65nvpmx/a/fe99L+vpI/GSh99Ln3xbV1gopMQgAAEIAABCEAAAhCoCwII0bqYpubp5KQdpH/PKS38r+C/ReaRFpgrEKCTT1Y7Dr/8GojSDz6T3vhQeu394L83P5J++712/aJlCEAAAhCAAAQgAAEI1CMBhGg9zloD9XmuWaUVFpGWXzj418Jz0o71M8Dxf0jvfiK98Jb09GvBf298EOy+YhCAAAQgAAEIQAACEIBAOAGEKHdGVQnYrbbzslLnZaQVF5VmnamqzVelse9/kp55XXryFemB56SX3pZaEaZVYU8jEIAABCAAAQhAAAL1QQAhWh/zVLe9nHxSaY2lpM7LSWsvK80/Z90OpeSOf/Wd9ODzgSgd/Zw09puSq+JCCEAAAhCAAAQgAAEINAQBhGhDTGO2BjHVFNJ6y0tdVpPWW0GaZsps9a+WvbHL7nOvS7c/Kt3+WBAICYMABCAAAQhAAAIQgECzEUCINtuMV2i8jlS74crSlmsGO59TTF6hhhqoWrvrjnknEKU3Pyh9iChtoNllKBCAAAQgAAEIQAAChQggRLk/SibglCorLy5tu4602erSdFOXXFXTX2hR6jOl190v3fqw9MO4pkcCAAhAAAIQgAAEIACBBiaAEG3gya3U0GaaTurVRdphPWnu2SrVSvPW+8tv0l1PSCPulh56oXk5MHIIQAACEIAABCAAgcYlgBBt3LmtyMg2Wlk6+0BpxmkrUj2V5hB4bIy0zyDp0y9BAwEIQAACEIAABCAAgcYhgBBtnLms+Eg2XEkadpTUYZKKN0UD7Qh88oXU5WDpf1+BBQIQgAAEIAABCEAAAo1BACHaGPNY8VF4B/Txi9kJrTjoiAYeeVHa+ohatU67EIAABCAAAQhAAAIQSJcAQjRdng1bW9/tpcN3adjh1cXALEQtSDEIQAACEIAABCAAAQjUOwGEaL3PYJX6f9dZ0jILVqkxmgklcMWdUr8hwIEABCAAAQhAAAIQgED9E0CI1v8cVmUE79wgTTNlVZqikQgCT78mbXoIeCAAAQhAAAIQgAAEIFD/BBCi9T+HVRnB53dUpRkaKUDgjQ+lNfcGEQQgAAEIQAACEIAABOqfAEK0/uewKiNAiFYFc8FG3vxQWgMhWvuJoAcQgAAEIAABCEAAAmUTQIiWjbA5KkCI1n6eEaK1nwN6AAEIQAACEIAABCCQDgGEaDocG74WhGjtpxghWvs5oAcQgAAEIAABCEAAAukQQIimw7Hha0GI1n6KEaK1nwN6AAEIQAACEIAABCCQDgGEaDocG74WhGjtpxghWvs5oAcQgAAEIAABCEAAAukQQIimw7Hha0GI1n6KEaK1nwN6AAEIQAACEIAABCCQDgGEaDocG74WhGjtpxghWvs5oAcQgAAEIAABCEAAAukQQIimw7Hha0GI1n6KEaK1nwN6AIFmInBhP2ndFcJHvMdp0gPPNhMNxgoBCEAAAmkTQIimTbRB60OI1n5ib31Y8ssfBgEIQKAaBK48Wtpw5fCWuh0v/fepavSCNiAAAQhAoFEJIEQbdWZTHhdCNGWgCap75jXptKukh19McFEdFJ1+amm2f0gzTCvNMI00/TTSlJNLk3b8+78O0i+/Sd//JH33U/DvNz9Ib38k/Ta+DgZIFyEQQWCySaWZppM++yrbiJpZiE4yiTTjNIXnx79DP4yr/hxOPqk0zZSF2x33q/Tzr+X1rdOMwfjKrae8XlT26o4dgufQzNMHzyA/i6adSvJ3tO1Z5B6Yw1/Poh+Dfz8eK332dWX7Ru0QaAYCCNFmmOUUxogQTQFiwipefEsaMFy675mEF2aw+GLzSpuvIS00t/TvOaQ5ZpGmLvIiFTWM336XXnlPev5N6dnXpbuekH78OYODpksQyCGw8L+kAX2k5RYOXnK9sHL5HdIZ10i/Z3BxpZmF6BL/lu4dXPgW/vBzaYVdq3+bn7yntPvmhdsddLU0cETyvnWYRNpvW2mProE4++MP6YW3pcOGSGPeSV5f1q7YbTNp0XmkBeaW5p5Vstj2mEux/30VPIf834PPSS++XUotXAOB5iaAEG3u+Y89eoRobFRlF3z1vUCAWmA1ivXZWjq2Qi9sP46TbhwtXX6nZHYYBLJIwAsw958rTTF5fu9uHi3tNTB7vW5mIbrm0tL1Jxefk80OlZ56tXi5tEpM2kF68SrpH9MXrvHiUdJRFyVv9ewDpR3Xz7/OC4AbHCC99kHyOrN0xXs3SlNNUZkevfCWdMWd0s0PNvYucmXoUWuzEkCINuvMJxw3QjQhsBKKv/VRsIJ96yNSa2sJFWT4kkoK0fbDHvWQ1G+I9O2PGYZB15qSwPBjpfVXjB76podIT7+WLTTNLEQ3X126uH/x+Rh2u3T40OLl0iqx0UrSFccUr23kfdL+ZxYv177E0gtId50ltbSEX/fg89J2RyWrM2ulKylE28b65XfSIYOl/zTQYnLW5pH+NA4BhGjjzGVFR4IQrRze9z6V7EZ102jpzwYToG3UqiVE3Z7dpQ44S/JLEwaBrBB4c2RwBi3KThwmnXdDVnob9KOZheguG0sD9y0+H199Jy3ZXRr/R/GyaZS49Ahp09WK12SPmh4nFi/XvkTvrtKJvaOvGfeL9O9t6vs5VQ0h2kbw6nukoy/i6Eiyu5DSzUYAIdpsM17ieBGiJYIrcNlHY6WzrpGuvS84h9PIVk0hao5//Cntfbo06uFGpsrY6oWAz4O+f5PkwChRNvh66eTLszWiZhai+28nHdkj3nzsdGx1zvI7kM6Y4UEgnWL2+Bhpi8OLlZr4837dpIN3jL7mzz+lebcOgsjVq1VTiJrRs29I2xwhWcRjEIBAPgGEKHdFLAII0ViYYhXyjt3Z10oj7kkWoOSfM0uffhmricwVKiREvXM58l7pg8+ksd8EEXJ/+VX6/Y8goMt0UwU7SQvOJS05v7T6UtLyC0e7j7UN3meaup8gjX4uczjoUBMSePB8ycGKoswLJzc9mA6Y6aYOInuWa80sRI/ZVdpn63gEb3hA2mdQvLLllOqxiXT6PvFqeOVdaZ394pVtK+WdVu+4Rtk7n0irFtgxTdZabUpHCVEHC/NC0BsfSp9+IX3zYxAp9/ffgx1gB9fz92q2mSQHsrIbs1MbzTht8XH4GeR0R1kMSFa895SAQGUJIEQry7dhakeIlj+VX3wjedfDwQx+/T1ZfTusJ+2/rbTqnsmuy0rpQkL0+Muk829M1tP5/hlEjey5idShwC6TXyT84mSBi0GglgS2XUc67+DwHrz9sbTOvsl/F9rX5sjUG68S/Pflt9L2R5c/2mYWomfuL+28YT5D7wZOMdnEf//pZ2mxnSsfoOaOM4JFuPYW1h9/bo+b5Xsluwe88PfAedICc4Vf5yMP196brM6slY4Sot6x9G5vEvPO9EYrS4d1k+afs/CVQ26UTrgsSe2UhUBzEECINsc8lz1KhGjpCL/+XvJD6NLbkr+oOFfcgH2kbdaW/veltHRMV7HSe1uZK9MWom29XPLf0tB+hV8Crv2vdMDZlRkXtUIgCQG/sB6w3cSLJ470vNfpwU5MEnNAmZUWkzZeWdpkFWnu2SZcnVZQmWYWosOOlDZZNX9GHOF4y875f99zgHTLQ0lmMFlZR11+9MJ8TxB7k2y/Xn5d3hFfYLtkbbi0U2xdfLi0ULvd+/HjpbNHSgOvTl5f1q5IU4i2jW2yjtLBOwXf7ahAT/bQWauP9O6nWSNCfyBQWwII0dryr5vWEaLJp8qJry+4WbpoVGnBCuz+c9Fh0nxzBG0jRMPnwHngbjo1ehXf55o2OLAxcuAlvwu5ImsE5p1dWmHRwKXP7pNPvlqay95s/5BevDJ8dAjR8mf95tOkVZfIr2e/M6VzD8r/+z1PBkcBKmWHd5f67pBf+y4nSFeGRNH1794cm5cWWGjySaWVF5cWnVf6/Gvpmdck50xtBKuEEG3j4mBPJ+wRLUb/87jU86RGoMgYIJAeAYRoeiwbuiaEaPzpdV7Li2+Vht4UnHcsxXbfTDpmN8kvBG2GEI0mOc/sks/g5brMtV3h3egjLihlJrgGAtkkgBCt7LzYRdVCLNfs7vrwBdKUOflgfxsvLdlN+uaH9PvlXbanL5XmmnXiur2L3usk6bGIfKELblf6Myj9UWSjxkoKUY/wjP2lbiEu3f7MQfR8jzi9CwYBCAQEEKLcCbEIIESLY/IZk8tuD9xw7Y5bis0wrXTOgcG5k1xDiBYmarfHgyIiPn7yhbRsz1JmhGsgkE0CCNHKzssLV0izz5zfxoLbS5f0l9ZcOv+zQ8+Vrrwr/X55Z9Y7tLl2zkhp+N3S0xFnD5ffVfqoQXYy06JaaSE603TS4xdJfpaHWd9zJKd1wSAAgYAAQpQ7IRYBhGg0JgeLuPI/0uDrpC++jYUztNCKi0oXHCrN0Sm8DoRoYbazzCC9dJU0ySTh5dbfX3rpndLnhyshkCUCCNHKzobT7eTuerrFf20p7bWl1H+X/PYff1na4rD0+3X2gdKO6+fXu3HfIG/yCxEu2uvuJ738bvr9qecaKy1Ezea0PlKvLuGU7n5Ssjs1BgEIIES5BxIQQIjmw3LwAa9snjVS+uyrBDBzik7SIjln3aE7F84ziBAtzvj2gcH5uzDrM1C6cXTxOigBgXoggBCt3Cz5SMSHt+TX39oqzb6ptNQC0t0hAdB8LtO7kPbASMsshp07dNqpJq7RqbyW7SF5B+7Va8Jb2+pw6dExafWkMeqphhBdaxnpuoizoA5WtMoejcGSUUAgDQLsiKZBsQnqQIhOmOTxf0jX3SedeU0QIr8cc6CdIYeEu3nl1osQLU76pN7SHl3Dyx1/qXT+TcXroAQE6oEAQrRyszTrTIF3Ra79/Ks0z1ZBMJqXh0szz5Bfxik6fDwjLduqszT00PzaLrlNOvICaeoppHcj2ut5ovSfJ9LqSWPUUw0hOv3U0pvXhfP66RdpvoRpYhqDPKOAQDgBhCh3RiwCCNEg0IBD9w+6Wnrvf7GwFSzUedkgr6BdSuMYQrQ4JedaPTLiLOjQm6XjLileR1iJf0wvLTV/sBPiHKY+/zPDNJJfOMb/KY37OXCR82r3Qy9IT7wc3C8YBCpFIOtCdJkFpTWWCr4zFnbTTRVED3eglhfelOzG6ojB3kXMmjmFyUND83v11XfSojsFf/cCotNq5ZojIa+zX3ojuvYEae3l8uvbqr/06EtSh0mkT28Lb68R8n6mRzKoqRpC1O18eLM0eU6+2bax/Hub0iLp+3qeRWnfEdRXawII0VrPQJ2038xC1C9Ktz0qDRohvflR+RPWsYPkUPz7bB19njGsFYRocfa7bCwN3De8nANJ9Q95uYyq1S+jW6wpbb5G8WTluXU4cuZ/nwrS97zyXvF+ty8x64zSk5dGu2mfe4M0IGS3JlkrE5e+oJ+06WrhNXgcvU4up/YJ1/bYRDp5z/C6Hng2nfQXzoG48Sr5bZx1rXRGhAtjktFdc0IgsMKs8z7S2x+Hf7b4fNJdZ4V/9tLb0iYHh3/mxY5XIvI3Ttox/Jo/W6U//ig+qsfGSNsdFV0uaR5R/7bttIG091bBgk0x++Az6eJRQcAd7zZmxVZeTBp1en5vPh4rLdcr+LtFqMVomK25d/K8sGH1WMA/f0UgNtubBfES3SYsdn08Sgq7F46+KEgflsQuO1LaYMXwK7zDeu8z4Z/tsJ40qIAAP+Rc6dp7k/QkvKx/40/ZK/wz5zvtepj04tvR7VRLiI65Suo0U3g/FtkxWUDDWj2L/Fvt3+wwc2yMZXpIP4wrf07balhwbun+c6Pr2/gg0rClRzs7NSFEszMXme5JOUL05gelm0ZLyy0s7b1l9Cph1gD4PNBdT0inj5CcdD4Nm6uTdMFh0vILJ68NIVqc2X7bSkdF7Ih6J3vgiOJ1rLt8cF7XOzrlmt24L7k1aNe7QXHtiqPDIyf7er8M+xya7880bKoppFdGSP43zCxC73wsjZakOWeRnhkWnmfPLmsLbV9aTs223vk83atXh48ljZ2qqSaXXh85cVqltrbf+khaPeIF2WWcF/jeweEcX3wryHUbZtNPI705Mh3+ubV4R807a1GWRIiuvqR0+r7Sv//Oe5ykx299LO01IDuBdRy13N/BXGs/x96ZsntuWHC0s6+VTk1hsciLlcfsmt+PEXdLB7W7l966Tppu6vxy/t3x714SSzLnufVedWy0iHVebQv0z75O0puJyzqK8cND88/LtpU6fXjxxaZqCdGoHdG/8rt2jecJUOtnkRfP7isgDA86RxqRYgRgP3cP+dvjIPcu8TvY2hGLzKXfUVyZBQII0SzMQh30oVQh6pX+DQ6Q2t6ZveLlZOBLL5DtQd/3TLDrVGhlNekIuqwqnXWA5BfLUgwhWpyak4nvuUV4OecRdT7RQnbDydIaIWkZirdcuMQ7nwTRNMd+E68mv4BcfXx02a37S4+8FK+uYqW863thRKRPu1Eu3V36PcbuWrF22j6//zxpsZD8jP7cjOyyWar5O+YdnTCzcF/B6SzKONe9/orS8GPD6/eO62kFxEejCtEWSf17SHaL99nJUs3B3+xKetODpdaQ3nXe3Tunb359fp6tf8CEv98xSFp+kfxy3uldcbfy+zN6iLTIPPn1bH+0NPq5CX+3IJ5lxvxy3g31rmgSK0eI+piJ8zlbpIeZvSu6FfhdK9ZPf/f8HQyzp1+TuvYrfiSiGkLUiwJeHAgze8ssvEOxkUpZeRbZiyNqUTbtKNGPXCAtMFc4m1J294tTpkQWCCBEszALddCHUoXofx6XeuZEj+vQIXhpcc7HySJcy2qF5OEXpAHDJT/U0jJHYDx+d6lnl/Je1BCixWfkhlOi3SZ3P1W67ZHCdTg/4GarF2+nlBKvfxAIrTgJ7x1J2bkB54xI5WMXN7+0p2EWbhZwYWbX4mNLPFcb1Te7pfeNeBGz66x3NUq18w+Rtg45t9dW31EXShffWmrt0ql7S7tuGn79evsXdhtrVCF65v7SzhuWzrT9lb+Nl3Y6Rnr4xXTqK7UWp2fxb3au+ey3XT/brJAHRpeDpWdeL7UH0TvozlFtt1x7W7TZU5dK/5otv62R90r7J/ydKEeIugebrCINK+Duvd+ZQbC/pLZ1Z+n8kKBNrufHccG5XC8AFLNqCNEo1273zc+BtfoU62WQqzYLzyJ/t/0dDzMv7nnB5cMUctUuOo/0wJDwdrxItdQuydyZixOmRFYIIESzMhMZ70epQvSXXyWfmwoL7uNdEe+OLjZf7Qf/xCvBDqjPTKVp888hXdQ/egcoSVsI0cK0vAptt8yws1J+YC7VXfq8yI6kH/x+Aci138dLr74fBFnxQ/fbH6TvfpKmmUqaZXpp3n8GrrROpVDIvNO+U8SOWu51B+8o9esWXpvdfBffufxzddNMGZw/nCIiqIZfmPzilKYtu5D0nzPDa/QC0KYR5+6K9cHz7vkPc1Fsu9a7yN5NLtWeukT61+z5V/ue8G5rIWtEIeqXxyN6RI/abogO2hV1ljXsyu9/CnYd308hIFyp89y/u3RgyGJJ7vd3wbmkhy8Ib8XeF/bCKNVO3EPqHeLdMfwu6eAcd8kHzpMWDfEyCFsILtafcoWo6x/cV9p+vfCW/Nu5Zh/p8wQuut5htUtu1E6rxbZFdxyrhhC1O7XdqsPMOccPPa94T7PyLHJU5hevinaH9sK9MwiUa4UWKG9/RNrt1HJb4PqsEkCIZnVmMtavUoWoh2H3jS0PDz/T5hcU74x6h9SBLqptz70RCNDRz6ff8nbrBomt/UOehiFEC1PsvrE0KOIMyZsfSmvsXXwWfMbQ5yWnnjI4q3j/s9IN90v3PCU5OEMh807/pqtKJ+9VOBLyzsdJ9z5dvC+z/0N6dpjkesMsjbyohXYZnn9T2ijEPbF4zwuXsPumU2M4dVGueZdnoR2CHY6kts5ykgMJFTLXv9hO0rc/Jq09OPv4WISb49CbpOMuLVzyvbapAAAgAElEQVRnqULUv5FhgaQctdm/L2H22vvSORGuge3Lf/Gt9EiB3cdCosS/m/7tbi8yveBjl9EbHgh+951f2YGT/jFdsFjjl2vvWM8c4brZ1rdRD0u9T0s+R2ldYa69uuTXFtavxy8OD8xktl78KiV6tp+FL1wZ/juy7ZFBZO72dvsgaYUQF+HHXpK2TLjwkoYQdc5TuxVHeXTc/aS0S5Hvavvx+eiAjxCEmb1c7O0S1yotRO3l5d+JuWYN79Eep0m3Ply8t1l6Fp2+T3TQorTyokZ9j0zKi7deBMIakwBCtDHnNfVRlSNE3ZkjLwyCtkSZQ/yf21da6F+pdz20wjHvBC6AFhhpm4XngH2kbddJt2aEaDRP7+z5QRYmbnxVEhdT5+yz+Dv58niuXrm98kv3zQMkRzoMM0deXqNAUJv211x5jLThSuH1+IXfZ8XKsUIvnV619+p9JayQO6dfUP2imtTO2E/qtlHxq/YZFAilpLbH5tJJERF/vYtbzJ2/VCEa1c9ap2+xwGofzfWND6UDz5a8uFfILKAH9JG2WCu6lAWtd0X9O10LixI+V98j9T1n4h4dt1sQJTjMdjwmWMxKalFnkX1me8l20XLb6h15ouR0YLn28rvSuglTyaQhRN2PVZeQbjwlOjJ8n0HSjTG+h/798+9gmDlllj2uvMsa1yotRPfdWjo6wjvCrudenLB7dRzLyrOo0G+Xx1EpN3TX7Tletme84E5xmFImewQQotmbk0z2qFwhOu6X4IFR6AyHz1LaFdEP9dxw9WlBsZuhIwne8eiEAEpp1e16HGXuosNLixxZrB8I0WhCDiziACNh5p3NVfaIH6TGud9+LbL7WWyuvBo++rzAdTfMvNPoHcditv4K0vDjwktZCCzbo/QolN61sFuuv3e55lQaS3aX7CZZCYuKSuq2fIbTZzmTmCOXOl3CzO1y8lrMWBwtnLO4lXQHpa0f3m31rmuu2cXQ55eKRTFuNCHanoNFuNPA+Hc+rjlwm1O9RFkpbqVx2y5WLkrYOdXMUTm74istKt06MLzG6++X9j2jWGv5nzsFkdNG5dplt0n9Q9x9o855f/R5EGE7iaUlRN1mIZHus/KOolsogJvd7O2S60WXXPNO+/ZH5e8OFxtrJYWoA0vdPjD6d/+quySnsYlrWXoW3X12dJDJy++UDos43xlnrM79ba+4MLNnxylXxKmFMvVKACFarzNX5X6XK0Td3UfHBOezir2wObXJ4IPSFXPvfByEsb/locBVrBK226bSsbuHv9in0R5CNJxioUi5viI31UEacxGnjkJnXuK4croNC6xnLpPmmCVi7JdJQ26M05v8MnYd9xntMPOOoXcOK2V2O3v92vCzqRaPfkFNYqssId2S48rpSMXecck9Z/vTz5Lz+P36e/wWfIb2Dfd38vxrht0hHX5+8boaVYi+/E7g/pl00cLup47IaS5h5gAli+2cvN7iM1G8RNRL99kjpVOvnPh6u5q/eKXknJ+55rPcdgUv5tbf/jrnjR0zPDzNWdTO03kHh3vgOGXKgtsXH2/7EmkKUS9y3XNO/mJQW3t3PCbtWiBHcSEvhyReLu3HVykhatf9UQPCoxe7fS+Irto7ncA+yWY0yFseFSAu7rPI3iaejzDzjvQS3SV/Z0uxqGBbfldcpbf03qel1Mo19UIAIVovM1XjfqYhRD2E/kOly24vPhi/+HmVbPfNol17itcS7MA6EqfP+ZVyVidOG3Y1O/tAaeNV4pQuvQxCdGJ2Fmd+MK4dskvVVtKJ3x3N9NMvS+de6pU+H+UznmGWRGw5r5rzq4WZzwLa06AUG3GctN4K4Vduc0TlI5dGtf9XYKldkgUzOam3tEfXicfiyMIWoteHvOjGPafbVuO6y0lXR5xpi8uqEYWoX0B99jpuWqLcu83upN59jLI9TpVuLRLpupR7v9g1US/GdtcffH3+1afuJe26WXitPuvqs6VxbZeNpIEhL/wO3rRSSCRf1xt1ptXBov65efHF3/Z9S1OIul57CTk42WQhnhf+fLdTpNsfzafjxaWbTw2PNO+ckhv2LU34VEKIOrKsd38LBUo793rppMvj3gXplkvjWeS4CT7b72MwYbbrKYGnWVJzKj8v/IRZbpTqpHVTvj4IIETrY55q3su0hKh3I/ziHDfc9yqLB/ncwkLTF4LyyReS8/pd89+Jw9ynDdIBIi7oFx2UIc32EKIBTecZ675R4NZn99Ioc2Aauws+mlK+zVLm8vGLpPnmyL9y/Hhpnq2DVfJi9s+Zg13RqKBF6+0njXm3WC0Tf+5cti+PCE+f9KFzIO6e7OU1WetB6V02lgZGBJeyO6PdGuPac5fn7xo7sugtD0pvjsxnl9RF7uQ9pd03z++Nz3ot7jN7MfKsNqIQjZObt9gcRn1HfN2Ft0jHXFyshvQ/9z0Tlu/ZO9/eAc+11ZaQbooIrnTXE1KPAmI7t67bBkorLprfRqHURkf1lJxKJswW2C7ZrnLaQtR9OmC76OjKXsRYfS/Ju7dtZpdURwL2LmOu+djEhgdKr5UYzTstIerdXgcRczqnsFyy7fv94PPSDsfU9pxjGs8iBwN0UMAwS3qft9Vx7K5Sn4gIw05R5gVFrLEJIEQbe35TG11aQtQdcpRG7yLE9ZB18J9jdpN6bFw8D6fPa/lMgV80S3UTiQPN7lg+02C3v2pF+21UIeqAJI4i611Cj3Hcr9LPv0h/Spp+quCF0LufSy8oLbdQkODd/AuZd9X80uizK7W0K44O0rqE2dr7BClh4thVx0obRCRyL+Vlfcf1g138MHMQL7/0VtocFfj5K8Ln0nkGnW8wjjnZul08c80uXe9+EuzGOGVMe/PLr8/AFjsm0HZN1EvcNfdIB+YEr4nqc6MJUZ/xW6ZH+SmECokoPyu2PiLOXZBeGbvDf3Kr5Fy+uRYV6MrXeLdolnZnlNuu9XNo0Z2kH2JEgvbZ8qcvDf9O2K3T7uZhduD2Uv9dwj9bvlf88/GuoRJC1HzsthomsN1m7vGJw7oFEZnD7JiLpAtHlT7fUULUi0kj7gmeQz7K8/244Dn082+SjxLYZXrGaYM0Of7N8SJ02GJFbs8csX2zfskCKpU+uugr03gWLTV/4GodZl5U9W9q3EBMbXXYaygsurLd2p0vN8m580pwo87KE0CIVp5xQ7SQphA1EB9sTyoS1lpaOuvA8PNyjiZ43vXBanWS8zilTI5fNoYcIq21TClXl35NowrR0omEX+kXikPOkxzhstZWKDrszsdK98YMSW8RajEaZn+lidgl3q5c2/XXnhDu0mxXPufD/PiL6pD77znSkvPnt5XkXg8LdGGPCEdatDnPpXdkci1upMd5ZpOejEjN0u046b8xUvG47UYTokkWCwrdTYWionp3foXdqnMvtrViseHzy2FWKKKzd/e9yx9mcVMteXHT93OuFUul5N1679qH2Tr7Sq+8F59hJYSoW59ndun+c4PUWLnm350uhwQRl+399NDQ8PPj3ll0gKK4i9hho44SovEJxS/pvNM7HptcnMVvIX7JtJ5FUb/Zf/3WXiA5f25cW25h6c6IYF61iu0Qt++US48AQjQ9lg1dU9pC1KtdnfskW6k1YJ9PcJAVi0Ant/bOxv3PSDc9WJ2VM7c75ODogASVvAmSvJxXsh+l1G3XG7vgVNrs8r3PQOmp1yrdUrz6T907cN0Ksz0HBMGz4pijSD8zTLKbbpglOfPoF+2Xh0sdO+bX5Bc9uzNXy3z21Wdgw8zuem99VLwnztmX68Jndy67ddnWXDr8nOjg66STY0Rj9Px5HnPNO1ze6YrredFoQrTfEOmKFDwOLFCevCR8nh1Qau4tit8DaZZwvtMnItyBnQ/7sTHhra22pHRTRD7LuBGALdQWmy+//mIv+I4Y7iMsYVaoz2HlKyVE3ZaPVAyKCHjzwlvSxn0lRwAOi7fwzffBsZ7Pvi5vtqshRO1p4VgYx19WfgT28kY74eq0nkWFjlRYePvsblwrFGgwTkqsuO1QLtsEEKLZnp/M9C5tIeqBOSm3k3PXg9n91u5C+25TXvCkcsaKEI2m5wUJu6h6R9znkCttM0wrLThXIIDm+2dwDtQicarJpSmnCFy5/N9UU0SnIjpocOCSFtcKibZRD0m9B8SrqdDLYBJxHK+1wqW8G+oV9jA7Yqh0aZHAZk7N8mBIxNr2AWJ8lss7XJ6L9maRa7FbzIYfKzmvY6558Wvv04tdPeHzRhOie5wm3ZogCE8Uqck6Sh8VcLWcfbPqnq2LcvV2/x34LCq3qV15X4iInvvLr8GixU8F0tssMKf0SEjaIrs8OvfkVwVyT266qnRpxLPU51N9fi+uVVKIug9R3yd/5tRKm60e3tOooEZxx9VWrpJC1ALUx0wcXfmZ15P2rLTy1X4WeTPAbuhhO9seQdwFRB+vef5yafaQxdW3P5ZWi9jhL40SV2WZAEI0y7OTob5VQoh6eM6p5fOcWbY5Z5EuOCw4E1JLQ4jm0/cOqNOXOChVubk/C82tXzId3W/dFaR1l5d8VsbnnsqxpO7pPif7tIMWhbRrd3Sfp4mTQsNRZL1LmGvf/igt2S1ZWpNyxt927QtXhL+MxAl+cfCO+elZnDR+0R0nPpMX9fJb6Nyd+xclYv1Z0hfjRhOi3Y+X7nkqjTtA+niUNGnIDr1rn2/rwgIunR5MqMW5Yp0zNswctdbRa6MsKqiVyxeLnhu10HT3k5JdgguZj61cF5EGZf8zpZH3xadUaSHaaUZp9JDAoymu+ff9wIjIqnHraCtXCSHqwHgW0fayiHvuP2m/28pn4VnkXW0vaIZZ3LyfhfLvnjhMOu+GUglxXb0RQIjW24zVqL+VEqJ2b1urj+QzXVm0TVYJzqU6RUutrVGFqM8HxRV1Pg/5+JjAPc7/ObBEJc1phByaf5+to3N5ltp+UiHqdoYfJ60fkXLl4MHS8CI7rDNPH6xmh0XgtSuZ0ytV26LO1llUL7xD4bRLYa6MYe7FvTaVTgtxrz3xMum8AnlYOy8jjTwpn8jP3uHaMQisFdcaTYh2O176b0pC9K3rolNfzL9tvEA/ceehWLmtOktDDw0v5fyzhYKxeLHy9oj8u8V20B8eKi04d367cVLYOBiXg3KF2VEXShffWmzUEz6vtBB1S442e2nMIFQW/uvuJ/k4TxoWJUS9m+mzp2FBqnLbtfD0zvhjL0mPvSw9+Url79EsPYsKpVzxu9xyvYoHgotatDFbB0ErNSVUGvcIdVSXAEK0urzrtrVKCVEDGf2ctP3R2ULjnZBjdwvO9xWL0FqtnjeqED3hMumm0YGba6eZgl0oh+93ihO/9Dk6p//1fxai1TC/jPTuKu2zjeQV/EpYKUK0UGCXODnXem4iDYjIO7r+AdJLb1dipIXrtLC2wA6zTQ6Wno1wcYs6Wxh2nm6uTsFucu53+elXpU0jRIf7c8Lu0p5b5vfszselXiECtdBIEaLRdKLSpfiKagvRqDPBFipzdC0cFMyBdn2WOywKqBdWLGT9op1rdvN/+IL8v/uaxXcu7qXglFaPhFzvGpNGwa6GEHW/zj0oiPdQyMyqa7903VwLpW+x+7TPCPu3xS6ofhb5WI7n4esfJJ9T9XNo7LeS3a2rYVl9Ft07OAjAFmbFcisXcmO/50mpexEPgGpwp43qEUCIVo91XbdUSSFqMEnPy1USpgXRRYcHibizZI0qRB3Q4fwCu1LVngMH83Fu2M7LJm/Z7sF2cXUAmz9bg5eZKBe0UoSo3XId7j7sXI1flO06+MFn0f12MBUHVcm1V96V1okIIpKcQrIrvNL/2jX5Zzhdy2lXBfmAw8y71MfkBMAyg+Ud9Xds/hXOS+jUC+3Nu/FOORC1wOGXe7/k51pUGo9CI0eIRtPJkhANc/d2z32+027Cxcz3pO/NMHNMBMdGyDWnKnEMglyzh4M9HYqZUyH5fGqYJU3vVC0h6hzQFt/ue5QlzfdbjJM/TyuPaJy2yi2T5WdRj02k0yMWNUfeK+0fklKrjceqS0g3R+Td9QKfF/qw5iGAEG2euS5rpM6rVsl8mV5xtIvup1+W1c2yL952HWlAn+iD+GU3UEYFH30evGTXoxWKmpslIergNz5P6Hx+hcwBRB5/OXATfv1D6Y0PpP99lR+5OWp3xXWXIkR9nXPX+mU5zArtfnhn1y+rYWdMk7rvpX0PRuW4e/Qlaav+4a3dMSg/kXwhQX14d6nvDvl19T0nPNWPd7Us+nPNZ1AX2yneedz21yJEo++aLAnRE/eQeodE6o27EFgoANclt0pHhgQkClskMa2uh0n2dChmXvB6J+JM3bX/lQ5IcL6yWkLUbsgOVOaFqCj76rvgvSBNT5h6EaJZfxb9FbRouOQ877nmgIHeyY86unBaH6lXl/zrvvw7FVmY10Cx7wCf1y8BhGj9zl1Ve17oDE9aHbn/WWnHY9KqLVk9jqhpAVrMVShZremWtthZs0+6dVartnoQok5Y/t/BQR67KPNuoyMi3v5oPCFSCSFqgfTUpeGC8r1PpZX3CO/9bptJp4REifXurXcF7QJdK9t5A+nMA/JbdxCmBbaVLP7am8+6jhmef7Z44Ahp0NXho4g613TnY1KvkEAvO20gnRXSp1J/pxCi0XdXloTo4IOk7UNcRl//IBBFcezxi4No2rnm4GrO09ve5p1deiIkfY3LrrhrvJyZdjn/9Nbws/ZJ3cirIUQdmMpnWqNcO9vzifp+xpmHsDL1IETr5VlUKDdplNeIF0JfvEpyPvZcG3qTdFxEzuZS55vrsk8AIZr9OcpED5+5rPguURoddWQ8R8irpjlv20WHSfPPWc1Wk7f11KvSZgXOsyWvsXpX1IMQjdqVM6U//pBOujwI+uHd0LhWCSHqtq8+PojeG2ZR+dduPV1aabH8KxztcfeI/Idxx1luOe/Wvnhl+Iu073nf++0tKm/iGntJbxbIPeodztzze169d1CkXLHrgDUOXJNrcYJChfFAiEbfJVkSolcdI22wUn5fHZBm837x7vSo3XdfnRupea8tpeN3z6/XLul2TY9rb18v2d011wp5FYTVXQ0hekQP6YDt4o5MKsUVPqr2ehCi9fIsKpTqKCon9RpLSTecEj47a+5d+QCE8e86SlaLAEK0WqTrvB3789uvv9JmF13/GNnNsRrmaJrH7xYEx8m63Tha6jMw670M71/WhWiXVYNE6mH26+/SXqdLXplPapUSohutLPllJcyuuFPqN2TiT2b7R5CzLSw68U7HSvc9k3Rk6Zf3Domjf+bayZdLg6+f+K+OuOnIm+3NaRPWjjiz1FbupN7SHl3z29juKMkvTu3t5RH5q/Z/+ExpN+nL75KPHyEazSxLQvS2gdKKi+b31RGCHSk4jkXlt/W1ucG0bjlNWiXn2eqzzqvvKb39SZzWgjJRaZAc3dX5T+NapYXoCotKowZE51cO6+e3PwS70Z99HXcU0eWyLkTr7Vl037nh8TT8W7lsT+mznHe5qCjpz70hbXxQ+fNLDfVHACFaf3NWkx4XcsFIu0NOCL1zRBTNtNqafprA7c4/+vVidjm062E9WtaF6MgTo4MTnXKF5NxopVilhKjTrzw3TLLAzDW72C6xs/R7u+icUelLfO5t2V6Sg/bU2nx+0ztJuWaRbLHcZnbre/0aaZqc3Z/TrpTOGll4FKssLt0yIL/MRaOkoy+a8PdF5glyHeaaUwZteXhppBCi0dyyJEQdQMdRbHPthgeCnbm49uD5kgVprrUXtA5G4wWP3PgLpbyUR6V/+fAzaYXd4vZaqqQQnXpKyedhw44/+CzsV99HP5PTei/IuhCtt2dRzy7BsaYwO+4SaejNEz5xtFynD5slJBL9oedJV/4n/n1KycYhgBBtnLms6Eh6bCydvm9Fm5iockdcc+S1StjyC0sXHhYeYr8S7aVVZ5oJ5NPqU9x6sixEHZjoqUvCdwu9muszl84bWYpVSoi6L4d1lw4KCb7jz7odJ/336Qk9vv5kac2l80fg866nRkTbLGW85Vyz2LzS/efl12AviYV2mCCW11pGui4kdcoqe0jvflq4B94RHnOVNHPO+aTcs7V7dpVO6J1flwPNOOBMKYYQjaaWJSH68vDwF+XLbpP6R6RICRuZv5v+jubauF+khbYPXMEdHO+8g/PLOJ+v8/omsSiPAkfxdntxrZJC1Iu/Pnudaz764PRRXkR79MLwCNq+JiqwWNyxuVyWhWg9PovsDm5x6TgbufbCW9KGB07460qLSreGeHX5+bpEt8rnYk1yn1C2egQQotVjXdctRb0kVmpQ3/0YuOim4YrT1kcHdNhvmyBMfseOlep5Zeq1q5aj0JXiEliZHiWrNctCNCoojUc45EbJeU5Ltd03k04OCRDk+kqNmtvWF+fGdNCiMHfb9rs33v1/ZYTkncT25nvK4u29/5U6uvSvCzvD6VbsWmgXQ1uYe63zn/pFNo6dsZ/UbaP8ku3P7jly8vor5vNapqfkXeRSDCEaTS1LQvTjUfnfFfc86ZlNByty0KIw2/oI6ZEXpQsOlbbMOYfsM+gOHuZ8lUnMizNepMk1u0jOsbnk73scq5QQLXScYNjt0uFDg9757KjPkIbZD+Okzn2kj7+IM5LwMlkWovX6LIpaYPjrGdNb8kKf7bjdpL23yp+XG+6X9jmj9DnlyvomgBCt7/mrWu8t4hxMZNaZqtak7nlK8i5gGuYIbV55LiU3ZBrtl1tHkhftctuqxPVZFqKH7iwdslP4qH0m12dzS7WonISur1wh6jquOV5aJyRo0Y8/BylGHHU2atfF6We2OKzUkVXmulP3lryLnGuHnS9dfkfw17DcnicNk86NSF+RW9c6y0nXhCRMb9/G69dKdptsb8++IW1SxhmmtIWof9Ps1hlmSQPURM1mpURJbntZEaJORfFuRE7j4y+Vzr8p2X3v9CRO55Jr590g+ezzK1dLM0038ad3PyntEnJ/Fms57Nx02zXzbxt/t6kSc+571a7uuZ4I7p9TtHgRyDu3tsk6Sg8MiQ4e6Dys2x0ZL5pwGLMsC9F6fRb5bL935MOsfS7oJy+R5pk9v9TW/aVHXip2h/N5oxJAiDbqzFZgXFE7CRVo6v+r3PcM6fr7y2vBLolDDpEcmbNe7YyrpdPr9HyomWdZiJ59oLTj+uF3Rq6La9L75/KjpI1XCb8qDSG6ySrSsKPC6/fLrF9qLz5c2nyN/DL7nSldd1/SEVW2/NrLSteemN/GtfdKB5wVLIR5QcwLY23mVfcVd5Oc7iKOeWf41aul6aaeuPSoh6XepwXn17zTnGveGfcOeamWthC1K5xfqsPs+TeljfqW2tMJ11VClIT1KitCdI5ZpOcuD+dWSrTkfbaWjgnJ/ex8twefK911Vn5bjmDtSNZJ7ZwDpR0ifseW6xl/F7EScx4VidhjDPsdWnlxyQESfaYwzA4/Xxr298JUUk5ZFqL1/CzysQp7zuVaW7As5431OeZcc0q0lXYrfWEh6fxTPnsEEKLZm5PM9mi1JaWbqpzmwdHy7KL7+TfJsThfVb9u0v7bhrsvJq+xNlf8FUFxL+ntj2vTfhqtZlmIFhKL5aQN+Mf0QaTaqIjMaQhRBznxi3OYp8I190iHnCe9dk2+6PK5S7v/lXr2NY17IqyOySYNAhE5qEl7e+PD4Hdgm7WDRaX2Vkpgl/MPkbZee+J6vvhGWryb1HUN6aKQgEQr7S69X4Ybc9pC1O/on94W/ts29mtpiZDziUnnrRKiJKwPWRGihY6glCIQ55xFembYxAsnHr9/06/4j9Rzk4lp+EiKz8o5UndSi4oI7XrW3ld69b14NaY95903kgbtF9623ZPtphxmA/eRdsnh01bup1+kdfaR3v8s3pjal8qyEK3nZ1FUQDzf68v3krquGb4o4xgFjlWANS8BhGjzzn3ikXsXwkFd5p4t8aVlXXDPk1L3hK5KXtm+oF94GP6yOlODi595TeqS8/Jdg26U1WSWhWihiNBnXyudmiCXX3tIp+wl7bZZNLZSApKE1dZ/F+nAkGAkY7+R+gySbjg5/6qwFC9lTXCKF4e5GDqq7wLbSSftmb97fewl0gXtIjPG6UpUigSnzPA5Ld+v7e2V96R1ygzWlrYQdf/CXIj9d7/8WdB88W0cGtFl0hYlUS1lRYiuvqR0Y8Ri67ZHSnYLTWq3D5JWWCTeVcPvCnZKS7FCwcvsgm9X/DiW5pzPO7vk9B65C0vux6+/SZ33ld6NSFHjIDiOYDx7SGRwX+8ou1scHv/sa9vYsyxE6/lZZA8Te6uEBS3youuWa0ne6W5vDlK1XK/qpeuLc/9TpvoEEKLVZ17XLfbZSjo2QSj4tAZ7zMXShbfEq23jlSW7uMyQc8Yr3tXZK1XuOcUsjCjLQrTv9tLhu4RTeu19qXOR3JRhVzrHpXfVvCsfZUnu6UJzOPesks/ehAUtevZ1abmF8692JENHNMyibb+eNDjErXSr/sHf5+w0odd/tkp2O/w0YQChKScPdor9b3tz/tUt1szPmXz6cOmMa8qjVQkheuvp0kqLhferFFfS3JrSFCWF6GVFiPp764WQMCv1O7P75tLJe8a7d7oeFgisUizKDdh1tbnpx6k3rTn3b5/vz+UjRHicnbANVpLs1pvmb2iWhWi9P4ui3MNfeFPy75/TjrW3JLl549y7lKlPAgjR+py3mvXaq5R2Bcw9X1XpDvmF03kCz71e8v8OM6/EHdUzCHbS/gxZpftWyfo/HivZJXB8u5yQlWyvUnVnWYg6nc8dBSL27TlAuuWh+GQ2XCl4mc2NUptbg4OVDL4+fr2FSl57grT2cvHqSmN3L15LpZWyS/OY4fki3pGA7Zrb3p56Vdrs0NLaGXaktElOHmF7X6y6RH6O0rX6SK9/UFo7bVdVQog6f5/z+IWZI1Wus5/kdCGlWttPNYUAABidSURBVFqipFj7WRGi3TaUztg/vLdO49QW/bPYeNp/PuuM0vNXFl6UcnmfcV5x19LPyhVKsbbfGdJ1MWMtpDXnUelrPFa7CTvKdZznmj2bvJsWZr/8Kq27f7JjK1kWovX+LPKi550Jot/2Okm68/Ek3ybKNiIBhGgjzmqFx1QosluFm5aDPHhn9P5nA7czC07vCHkXdK+tot14Kt2vStXvHRq7Uda7ZVmIeifxuWHS7DOHU3YE2h2Olp5+rfAseAdgzy2l/t0ln3UsZg4+5SBUaViUq2lY3eUE+kijr3HquH2gtMKixUuWk9cz7LxpWIvvfBJE9SzXKiFEt+4snV9AiD/4vORzzqW66KYlSoqxy4oQdXqvo3qF93bRHaWvEqZUaavJsRUcY6GQnXmtNKDEYwCud6vO0tCIeyHJ9ySNOV9q/mBxL2wxzulkuhwsOaBWHPPClIPc+N8ws9eHF6NcbxzLshBthGeRoyMvMk/xmXCObrvlxlmMKF4bJeqZAEK0nmevRn2fZsogqmTUg6Fa3XJqCr/8F9t5qlZ/0m7Hq+8OUtQIP9RZFqKeN5/l9JnOKHNuv0tvCxYF3v07J1pb2X/PIa2/gtRzU8lnotrbJ18EKQsmDxGm54yUTrkynbvGQYuev6J4ZGiL6qW6S/43y+YAY0f2LNxDnxtduof0+deljcReHY6eW+z3Y/B10slXlNZG+6sqIUSnmjxI4RJ2Bq+tbQeksiD174l/M+054kWXpReQ1t1X+n5c9NjSECVxyGVFiB7dS9p3m/AeOxdnqb/Fu2wsDSxwxthnelfbU/KiR6nmvLfOfxtmSVzLy53zKSaT7h0sLTBXeF98ntvnupOYd0S9MxplSbxLsixEPb56fxYV63/bHA66WhpYx5kAkty/lC1MACHKHVISgWIP1pIq5aKJCPQ8UfrPE40BJetC1ELx7rPjreR6d8mBgHyN8+NNP034HDmy46aHBC9QC82dX+biUdJRF6U3v04C72Twhcy5OJ0vM+tmXg+FhPpv3+/HxkhbhkS3TTK2OC7NpZ4NzO1HJYSo2zh9H6lHRHTRYiyKpfUoV5QUa7/t86wI0agUZXZvnjcngFXcsbmcc4WOuUrq2DH8qlIiP+fWtMri0i0DwutPIv7KnfNCQdo+/Eyym/u4X5PQC8oWSgHjKMMbHBDPfT7rQrTen0Ve4Hvpqvzz9+1n3As63g31rigGAYQo90BJBJzf684zpWUWLOlyLipC4N6npZ2PaxxMWReiJj3fPwMxmsb55x/GSc5B+sQr0lXHShusmD+XNz4QRLVNyxzN+smLo1MVedfFgZfKPeuYVn+L1WOvC+f0jLI00t9031gaVGCnyme0/cKUhlVKiHohxK6LYSl8ivXb94MDckVZuaKkWPttn2dFiIZFbHYf/cK8VERAs7hjvOYEaZ2Ic9xpRNBefL4gQm2YOZXTgefE62k5c97ZeYBPCI/R4N8fH3EY/Xy8fuSWmu0fwX0e9fv80tvSxgcV37XOuhBthGfROX2lHdaLnufbH5F2q3IqwNLuOq6qBgGEaDUoN2gbi/xLuutsya44WHoEnEvOed/s1tkoVg9C1KwtFiwco1IGxJkP79Ttf5b00edBaUfMdOTMXBv9nLT90XFqjF/GSeAdbCfMHn1JcuTZerFCeREd9t95UL/8rrzReEf7xauiA8n4PLqjG6dhlRKi7pvn3DtG00yVrKeb95OefCX6mnJESZKeZEWI3niKtPpS+T1vy2ObZEy5ZaOiQdvt3/fy1yWeP21rx4s2XrwJszsek3YNSeMUVrbUOZ9xWsnnAy0Yw2zkfdL+Z5ZDUCoUTMo129XTLp+FrB6EqPtfz8+i1ZaQbjotehaSpBMq747h6noggBCth1nKcB+ThKbP8DAy1bW9Bkg3J4jSmqnOR3SmXoSou+8XqRP2kDZfPVn0ZZ9VHHKDdNGtE+e2691VOjEk2M3bHwfnwtK08w6Wtl0nvEbvsHunvV5sjaWkG04J763zOTqvYxo26nRp5YgUKMWEWpL2KylE3Y9F55XOPySee3lbv7sdLzmFQpSVKkqScHHZrAhR7yh6ZzHXyonO3FaXd/J8njf3vPjdTwbpVco1x2zwmecwe+RFaeuItDS55Uud84sPlzZfI7z9L78N4h1880O5owx+E/zbEGYW9d4VHfNOdDv1IkQ9gnp9Fs1j75yIRRGnDfNxBwwCbQQQotwLZRO45nhpneXLroYKJF1/v7RvgvDn9QLNCd3XWia8tw6kUiwibS3G6WAuO28orbe89M9Zwnvg9AH3PSvZ9c2RnMMiNy70L2mz1fKvd0L3c29Ib2QOuvP85dIsM+bX+eaH0pp7l54aIr1exq/J44k68+oXa7s9p2HeAQsTog6GdPbI6HRRSdvuNKPks/Vh5kWMq+5KWmN+eR+Z2GKtIM2Nd0lz86T6Co/LiyCPvywNuVH64LPodrdcU/r3nOGf3/Sg9G4ZwXXa1+potZNHeNY4xdFvv5fPJk4Ne2wefub7/f9JTh9UrjmwWe44x35d/s6++zVpB+mA7cN7aNfi4XfH630pc+7fHKePibLHx0iPjonXfrFSjpK/3brRpd76SBr1cPTn/k0JC1BmEXvOdcVar83n9fYs8vfopIhF1t6nFZ6f2hCm1VoSQIjWkn6DtO2H0OjzguigWOkEHMjBef98vhDLFgG7vfkMnl05/RLz/U/Sx19I73wcP21ApUfUcxNpwD7hrRx0jjTinkr3gPqzRMA7b3N2Cn6XLUgdPfer76SPxkpeBMEgAIH6I5D1Z5F/dx67KPjtyTUfV3Fe9LipdupvduhxKQQQoqVQ45o8At5RuP7kePkTwZdP4Kefg1xor7wHHQgkJ+CUHE9eEp6+xQ//VXpLXvHHIAABCEAAApUisPdW0nG7hdd+yLnpeH5Uqu/UWxsCCNHacG/IVu0uM7hvsnN1DQki4aC8OuhULfcUOKuVsEqKNxmBw7pJB+0YPuiDBksjYrrlNRk2hgsBCEAAAikR8DnlRy+UHLgq1z78XFp1D+n3P1JqjGoahgBCtGGmMhsDOWKX6HMq2ehh9npx7MXSBbdkr1/0qD4ILLWAdMeg8HNPPv+3au/iKQ3qY6T0EgIQgAAEskrg8qOkjVcJ713fc6SrOR6S1amrab8QojXF33iNt7RIF/aTuq7ZeGOrxIiuuFPqN6QSNVNnMxCYeoog9+kCc4WPdvdTpdseaQYSjBECEIAABGpFYOcNpDMPCG/91fek9fbnbGit5ibr7SJEsz5Dddg/R+9zYvANV67Dzlexy46Q67xqf7ZWsVGaahgCHSaRrjxGWm+F8CE5n+mWhzfMcBkIBCAAAQhkkIAjdI88MTxGSGtrkDrIeawxCIQRQIhyX1SEwGQdpcuPltYlrUso31sekvYeGKRSwCCQlMAkk0hnHSDtsF74lQ5MtMEB0qvvJ62Z8hCAAAQgAIF4BJacPwhUOcM04eVHPST1HhCvLko1JwGEaHPOe1VG7TDeVx0bnT+yKp3IYCN3PCrtMUD6g0P7GZyd7HfJqTiGHhp9FscjGDBcOvOa7I+FHkIAAhCAQH0S6LyMdOmR0jRThvf/i2+ltfaWvvq+PsdHr6tDACFaHc5N28oUkwVuulHug80Gxjuh+w4iclyzzXta451/TumS/tIi80TX+PwbUpdDWehIizn1QAACEIDABAKOBdJ3e+mQnSUfEQkzu+T2OFG6+0nIQaAwAYQod0jFCXTsELgROr1LM9ult0lHXij5BxqDQBIC9i7Ydxtp/22lKSaPvtIr0BseKH3yRZLaKQsBCEAAAhAoTmCp+aXT+kjLLlS47ODrpZMvL14fJSCAEOUeqBqBY3aV9tm6as1lpiELT7tKnnVtZrpER+qEgPOx+RzoXltKs/2jcKd//S0ICvH0a3UyOLoJAQhAAAJ1QWClxaS9twyCUE7SUrjLdz0h9TyJRfe6mNgMdBIhmoFJaKYu7LmFdOxu0e4cjcbCQWMOGyKNIH9Wo01txccz+WTSa9dITtFSzH75Tep1knT/s8VK8jkEIAABCEAgPoGDdpAO6x6vvJ9BFqFeGMUgEIcAQjQOJcqkSsCRdC/oJ003darVZq6yr76TnMfRaTQwCJRC4IZTpDWWKnzltz9Ie5wmPfRCKS1wDQQgAAEIQCCawMqLSaNOL07IOav7DJJ++714WUpAoI0AQpR7oSYEFpgzyIE43xw1ab7ijb72vrTLCdKHn1e8KRpoYAK9u0on9o4e4FOvSnudzpnQBr4FGBoEIACBmhJwQKJXRkgzThfeDXvkHHeJNOyOmnaTxuuUAEK0TieuEbo9/TTShf2ktZdrhNFMGMOdj0n7niH99EtjjYvRVJ/APLNLT16S367vrXOvkxwQ4g9y0VZ/YmgRAhCAQBMRGHKwtM06+QN2TIJ+Q6RX32siGAw1VQII0VRxUllSAg4DfuD20qE7SR06JL06W+V9HvTEYdKFt2SrX/Smvgk8PFRacO5gDD53c9190ukjpLHf1Pe46D0EIAABCNQHgc3XkC4+fEJf3/wwCMJ4+6P10X96mV0CCNHszk1T9WyVxaULDpNmm6k+h/3R51LvAdJzb9Rn/+l1dgkc1k1aZQnJkQgtQr8mOXh2J4ueQQACEGhAAtNOJd07WHr85eA5ROyLBpzkGg0JIVoj8DSbT2Dm6aVzD5bWqTNX3Tsek/qeI333I7MKAQhAAAIQgAAEIAABCMQhgBCNQ4kyVSXQq4vknKNTxUhbUdWO5TT2wzjpqAula++tZS9oGwIQgAAEIAABCEAAAvVHACFaf3PWFD3+9xzSeQdLyy6UzeE+Pkba70zpo7HZ7B+9ggAEIAABCEAAAhCAQJYJIESzPDtN3jcHL9p3a+mgHaUpJssGDEcrPe1K6ZJbpT9bs9EnegEBCEAAAhCAAAQgAIF6I4AQrbcZa8L+enf0zP2llRev7eBHPycdcp7kwEQYBCAAAQhAAAIQgAAEIFA6AYRo6ey4sooEnOalx8bSET0k5x+tpn31nXT8pdLI+6rZKm1BAAIQgAAEIAABCECgcQkgRBt3bhtyZLPMIB3TS9p2XcnitJL2x5/S8LukU66QviUibiVRUzcEIAABCEAAAhCAQJMRQIg22YQ3ynBXWkw6dW9psXkrMyLnA+0/VHrhrcrUT60QgAAEIAABCEAAAhBoZgII0Wae/Tof+ySTSDutLx3eXZplxnQG88kX0smXSzeNlohFlA5TaoEABCAAAQhAAAIQgEAuAYQo90TdE5hmSmn/7aTeXaUpJy9tOM4JOuRGaehN0i+/lVYHV0EAAhCAAAQgAAEIQAAC8QggRONxolQdEJhtJungnYJd0o4d43XYonPY7dLg66Wvv493DaUgAAEIQAACEIAABCAAgfIIIETL48fVGSQw7+xB7tGtOksdO4R38Nffpavvls65TvrfVxkcBF2CAAQgAAEIQAACEIBAAxNAiDbw5Db70OaZXdp+XWmDFaV/zS45Cu67n0j/eUK67l7ps6+bnRDjhwAEIAABCEAAAhCAQG0IIERrw51WIQABCEAAAhCAAAQgAAEINC0BhGjTTj0DhwAEIAABCEAAAhCAAAQgUBsCCNHacKdVCEAAAhCAAAQgAAEIQAACTUsAIdq0U8/AIQABCEAAAhCAAAQgAAEI1IYAQrQ23GkVAhCAAAQgAAEIQAACEIBA0xJAiDbt1DNwCEAAAhCAAAQgAAEIQAACtSGAEK0Nd1qFAAQgAAEIQAACEIAABCDQtAQQok079QwcAhCAAAQgAAEIQAACEIBAbQggRGvDnVYhAAEIQAACEIAABCAAAQg0LQGEaNNOPQOHAAQgAAEIQAACEIAABCBQGwII0dpwp1UIQAACEIAABCAAAQhAAAJNSwAh2rRTz8AhAAEIQAACEIAABCAAAQjUhgBCtDbcaRUCEIAABCAAAQhAAAIQgEDTEkCINu3UM3AIQAACEIAABCAAAQhAAAK1IYAQrQ13WoUABCAAAQhAAAIQgAAEINC0BBCiTTv1DBwCEIAABCAAAQhAAAIQgEBtCCBEa8OdViEAAQhAAAIQgAAEIAABCDQtAYRo0049A4cABCAAAQhAAAIQgAAEIFAbAgjR2nCnVQhAAAIQgAAEIAABCEAAAk1LACHatFPPwCEAAQhAAAIQgAAEIAABCNSGAEK0NtxpFQIQgAAEIAABCEAAAhCAQNMSQIg27dQzcAhAAAIQgAAEIAABCEAAArUhgBCtDXdahQAEIAABCEAAAhCAAAQg0LQEEKJNO/UMHAIQgAAEIAABCEAAAhCAQG0IIERrw51WIQABCEAAAhCAAAQgAAEINC0BhGjTTj0DhwAEIAABCEAAAhCAAAQgUBsCCNHacKdVCEAAAhCAAAQgAAEIQAACTUsAIdq0U8/AIQABCEAAAhCAAAQgAAEI1IYAQrQ23GkVAhCAAAQgAAEIQAACEIBA0xJAiDbt1DNwCEAAAhCAAAQgAAEIQAACtSGAEK0Nd1qFAAQgAAEIQAACEIAABCDQtAQQok079QwcAhCAAAQgAAEIQAACEIBAbQggRGvDnVYhAAEIQAACEIAABCAAAQg0LQGEaNNOPQOHAAQgAAEIQAACEIAABCBQGwII0dpwp1UIQAACEIAABCAAAQhAAAJNSwAh2rRTz8AhAAEIQAACEIAABCAAAQjUhgBCtDbcaRUCEIAABCAAAQhAAAIQgEDTEkCINu3UM3AIQAACEIAABCAAAQhAAAK1IYAQrQ13WoUABCAAAQhAAAIQgAAEINC0BBCiTTv1DBwCEIAABCAAAQhAAAIQgEBtCCBEa8OdViEAAQhAAAIQgAAEIAABCDQtAYRo0049A4cABCAAAQhAAAIQgAAEIFAbAgjR2nCnVQhAAAIQgAAEIAABCEAAAk1LACHatFPPwCEAAQhAAAIQgAAEIAABCNSGAEK0NtxpFQIQgAAEIAABCEAAAhCAQNMSQIg27dQzcAhAAAIQgAAEIAABCEAAArUhgBCtDXdahQAEIAABCEAAAhCAAAQg0LQEEKJNO/UMHAIQgAAEIAABCEAAAhCAQG0IIERrw51WIQABCEAAAhCAAAQgAAEINC0BhGjTTj0DhwAEIAABCEAAAhCAAAQgUBsCCNHacKdVCEAAAhCAAAQgAAEIQAACTUsAIdq0U8/AIQABCEAAAhCAAAQgAAEI1IYAQrQ23GkVAhCAAAQgAAEIQAACEIBA0xJAiDbt1DNwCEAAAhCAAAQgAAEIQAACtSGAEK0Nd1qFAAQgAAEIQAACEIAABCDQtAQQok079QwcAhCAAAQgAAEIQAACEIBAbQggRGvDnVYhAAEIQAACEIAABCAAAQg0LQGEaNNOPQOHAAQgAAEIQAACEIAABCBQGwII0dpwp1UIQAACEIAABCAAAQhAAAJNSwAh2rRTz8AhAAEIQAACEIAABCAAAQjUhgBCtDbcaRUCEIAABCAAAQhAAAIQgEDTEkCINu3UM3AIQAACEIAABCAAAQhAAAK1IYAQrQ13WoUABCAAAQhAAAIQgAAEINC0BFpm2aT19xapY9MSYOAQgAAEIAABCEAAAhCAAAQgUDUCrdJ4C9GvW6QZq9YqDUEAAhCAAAQgAAEIQAACEIBA0xJolb6xa+5zkpZpWgoMHAIQgAAEIAABCEAAAhCAAASqSeB5C9FhknpWs1XaggAEIAABCEAAAhCAAAQgAIGmJXB5S6curd3UqquaFgEDhwAEIAABCEAAAhCAAAQgAIHqEWhR95aZN2+ddpLx+kzSVNVrmZYgAAEIQAACEIAABCAAAQhAoAkJjPuzo2Zr8cA7dWk9X63auwkhMGQIQAACEIAABCAAAQhAAAIQqBaBFg0de0dLn7+E6ExdW+fq+LtekzR1tdqnHQhAAAIQgAAEIAABCEAAAhBoKgI/jZ9Ui3w9quWjv4SobdZNWvu2Smc2FQYGCwEIQAACEIAABCAAAQhAAAJVIdAiHfT5nS1nubH/F6L+P526tA5Xq3auSi9oBAIQgAAEIAABCEAAAhCAAASag0CLRoy9o6Vb22AnEqLq3DpFp6l0r6TVmoMGo4QABCAAAQhAAAIQgAAEIACBChN4dOw4rafRLb+EC1H/1WJ0al3CzmiFp4LqIQABCEAAAhCAAAQgAAEINDoB74T+pN3bi1APeeId0XYQ/j4zeiIBjBr9zmB8EIAABCAAAQhAAAIQgAAEUifwU4t0dNuZ0NzaI4WoC/4VTXe8+qtVPcgzmvrEUCEEIAABCEAAAhCAAAQgAIFGIzBOLbpifEed6ui4UYMrKETbLpp589ZpJ/lDXdWqdSUt1SrNI2naFqljo1FjPBCAAAQgAAEIQAACEIAABCBQnECrNF7SDy3S+5JeVIvu+7ODRn15a8sPxa7+P1dM6S1zCmkxAAAAAElFTkSuQmCC');
}

if (! defined('MIXPAY_PAY_LINK')) {
    define('MIXPAY_PAY_LINK', 'https://mixpay.me/pay');
}

if (! defined('MIXPAY_API_URL')) {
    define('MIXPAY_API_URL', 'https://api.mixpay.me/v1');
}

if (! defined('MIXPAY_SETTLEMENT_ASSETS_API')) {
    define('MIXPAY_SETTLEMENT_ASSETS_API', MIXPAY_API_URL . '/setting/settlement_assets');
}

if (! defined('MIXPAY_QUOTE_ASSETS_API')) {
    define('MIXPAY_QUOTE_ASSETS_API', MIXPAY_API_URL . '/setting/quote_assets');
}

if (! defined('MIXPAY_MIXINUUID_API')) {
    define('MIXPAY_MIXINUUID_API', MIXPAY_API_URL . '/user/mixin_uuid');
}

if (! defined('MIXPAY_MULTISIG_API')) {
    define('MIXPAY_MULTISIG_API', MIXPAY_API_URL . '/multisig');
}

if (! defined('MIXPAY_ASSETS_EXPIRE_SECONDS')) {
    define('MIXPAY_ASSETS_EXPIRE_SECONDS', 600);
}

if (! defined('MIXPAY_PAYMENTS_RESULT')) {
    define('MIXPAY_PAYMENTS_RESULT', MIXPAY_API_URL . '/payments_result');
}

/**
 * Add the gateway to WC Available Gateways
 *
 * @since 1.0.0
 * @param array $gateways all available WC gateways
 * @return array $gateways all WC gateways + offline gateway
 */
if (! function_exists('wc_mixpay_add_to_gateways')) {
    function wc_mixpay_add_to_gateways($gateways)
    {
        if (! in_array('WC_Gateway_mixpay', $gateways)) {
            $gateways[] = 'WC_Gateway_mixpay';
        }

        return $gateways;
    }
}
add_filter( 'woocommerce_payment_gateways', 'wc_mixpay_add_to_gateways' );

/**
 * Adds plugin page links
 *
 * @since 1.0.0
 * @param array $links all plugin links
 * @return array $links all plugin links + our custom links (i.e., "Settings")
 */
if (! function_exists('wc_mixpay_gateway_plugin_links')) {
    function wc_mixpay_gateway_plugin_links($links)
    {

        $plugin_links = [
            '<a href="' . esc_url(admin_url('admin.php?page=wc-settings&tab=checkout&section=mixpay_gateway')) . '">' . esc_html(__('Configure', 'wc-mixpay-gateway')) . '</a>',
            '<a href="mailto:' . esc_html(MIXPAY_SUPPORT_EMAIL) . '">' . esc_html(__('Email Developer', 'wc-mixpay-gateway')) . '</a>'
        ];

        return array_merge($plugin_links, $links);
    }
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'wc_mixpay_gateway_plugin_links' );

if (! function_exists('add_cron_every_minute_interval')) {
    function add_cron_every_minute_interval($schedules)
    {
        if (! isset($schedules['every_minute'])) {
            $schedules['every_minute'] = [
                'interval' => 60,
                'display'  => esc_html__('Every minute'),
            ];
        }

        return $schedules;
    }
}
add_filter( 'cron_schedules', 'add_cron_every_minute_interval' );

/**
 * MixPay Payment Gateway
 *
 * @class 		WC_Gateway_mixpay
 * @extends		WC_Payment_Gateway
 * @version		1.0.0
 * @package		WooCommerce/Classes/Payment
 * @author 		Echo
 */
add_action('plugins_loaded', 'wc_mixpay_gateway_init', 11);
function wc_mixpay_gateway_init()
{

    if (! class_exists('WC_Payment_Gateway')) {
        return;
    }

    add_action('check_payments_result_cron_hook', ['WC_Gateway_mixpay', 'check_payments_result'], 10, 1);

    class WC_Gateway_mixpay extends WC_Payment_Gateway
    {
        /**
         * Constructor for the gateway.
         *
         * @access public
         * @return void
         */
        public function __construct()
        {
            global $woocommerce;
            $this->id                 = 'mixpay_gateway';
            $this->icon               = apply_filters('woocommerce_mixpay_icon', MIXPAY_ICON_URL);
            $this->has_fields         = false;
            $this->method_title       = esc_html(__('MixPay Payment', 'wc-gateway-mixpay'));
            $this->method_description = esc_html(__( 'Allows Cryptocurrency payments via MixPay', 'wc-mixpay-gateway' ));

            // Load the settings.
            $this->init_form_fields();
            $this->init_settings();

            // Define user set variables
            $this->title                   = $this->get_option('title');
            $this->description             = $this->get_option('description');
            $this->instructions            = $this->get_option( 'instructions');
            $this->mixin_id                = $this->get_option('mixin_id');
            $this->payee_uuid              = $this->get_option('payee_uuid');
            $this->store_name              = $this->get_option('store_name');
            $this->settlement_asset_id     = $this->get_option('settlement_asset_id');
            $this->invoice_prefix          = $this->get_option('invoice_prefix', 'WORDPRESS-WC-');
            $this->debug                   = $this->get_option('debug', false);

            // Logs
            $this->log = new WC_Logger();

            // Actions
            add_filter('woocommerce_available_payment_gateways', [$this, 'chenk_is_valid_for_use'], 1, 1);
            add_action('woocommerce_page_wc-settings', [$this, 'is_valid_for_use'], 1);
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
            add_action('woocommerce_thankyou_' . $this->id, [ $this, 'thankyou_page' ] );
            add_action('woocommerce_api_wc_gateway_mixpay', [$this, 'mixpay_callback']);
            add_filter('woocommerce_settings_api_sanitized_fields_' . $this->id, [$this, 'deal_options'], 1, 1);

            // Customer Emails
            add_action( 'woocommerce_email_before_order_table', [ $this, 'email_instructions' ], 10, 3 );
        }

        /**
         * Initialise Gateway Settings Form Fields
         *
         * @access public
         * @return void
         */
        function init_form_fields()
        {
            $this->form_fields = apply_filters( 'wc_offline_form_fields', [
                'enabled' => [
                    'title'   => esc_html(__('Enable/Disable', 'wc-mixpay-gateway')),
                    'type'    => 'checkbox',
                    'label'   => esc_html(__('Enable MixPay Payment', 'wc-mixpay-gateway')),
                    'default' => 'yes',
                ],
                'title' => [
                    'title'       => esc_html(__('Title', 'wc-mixpay-gateway')),
                    'type'        => 'text',
                    'description' => esc_html(__('This controls the title which the user sees during checkout.', 'wc-mixpaypayment-gateway')),
                    'default'     => esc_html(__('MixPay Payment', 'wc-mixpay-gateway')),
                ],
                'description' => [
                    'title'       => esc_html(__('Description', 'wc-mixpay-gateway')),
                    'type'        => 'textarea',
                    'description' => esc_html(__('This controls the description which the user sees during checkout.', 'wc-mixpay-gateway')),
                    'default'     => esc_html(__('Expand your payment options with MixPay! BTC, ETH, LTC and many more: pay with anything you like!', 'wc-mixpay-gateway')),
                ],
                'mixin_id' => [
                    'title'       => esc_html(__('mixin id ', 'wc-mixpay-gateway')),
                    'type'        => 'text',
                    'description' => esc_html(__('This controls the mixin id or multisig group (minxinid_1|minxinid_2|minxinid_3|threshold)', 'wc-mixpay-gateway')),
                ],
                'payee_uuid' => [
                    'title'             => esc_html(__('Payee Uuid ', 'wc-mixpay-gateway')),
                    'type'              => 'text',
                    'description'       => esc_html(__('This controls the assets payee uuid.', 'wc-mixpay-gateway')),
                    'custom_attributes' => ['readonly' => true]
                ],
                'settlement_asset_id' => [
                    'title'       => esc_html(__('Settlement Asset ', 'wc-mixpay-gateway')),
                    'type'        => 'select',
                    'description' => esc_html(__('This controls the assets received by the merchant.', 'wc-mixpay-gateway')),
                    "options"     => $this->get_settlement_asset_lists(),
                ],
                'instructions' => [
                    'title'       => esc_html(__( 'Instructions', 'wc-gateway-gateway' )),
                    'type'        => 'textarea',
                    'description' => esc_html(__( '', 'wc-gateway-gateway' )),
                    'default'     => esc_html('Expand your payment options with MixPay! BTC, ETH, LTC and many more: pay with anything you like!'),
                ],
                'store_name' => [
                    'title'       => esc_html(__('Store Name', 'wc-mixpay-gateway')),
                    'type'        => 'text',
                    'description' => esc_html(__("(Optional) This option is useful when you have multiple stores, and want to view each store's  payment history in MixPay independently.", 'wc-mixpay-gateway')),
                ],
                'debug' => [
                    'title'       => esc_html(__('Debug', 'wc-mixpay-gateway')),
                    'type'        => 'text',
                    'description' => esc_html(__('(this will Slow down website performance) Send post data to debug', 'wc-mixpay-gateway')),
                ]
            ] );
        }

        /**
         * Output for the order received page.
         */
        public function thankyou_page() {
            if ( $this->instructions ) {
                echo esc_html(wpautop( wptexturize( $this->instructions ) ));
            }
        }

        /**
         * Add content to the WC emails.
         *
         * @access public
         * @param WC_Order $order
         * @param bool $sent_to_admin
         * @param bool $plain_text
         */
        public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {
            if ( $this->instructions && ! $sent_to_admin && $this->id === $order->get_payment_method() && $order->has_status( 'on-hold' ) ) {
                echo esc_html(wpautop(wptexturize($this->instructions)));
            }
        }

        /**
         * Process the payment and return the result
         *
         * @access public
         * @param int $order_id
         * @return array
         */
        function process_payment($order_id)
        {
            $order        = wc_get_order($order_id);
            $redirect_url = $this->generate_mixpay_url($order);

            if ( ! wp_next_scheduled( 'check_payments_result_cron_hook', [$order_id]) ) {
                wp_schedule_event(time(), 'every_minute', 'check_payments_result_cron_hook', [$order_id]);
            }

            return [
                'result'   => 'success',
                'redirect' => $redirect_url
            ];
        }

        /**
         * Generate the mixpay button link
         *
         * @access public
         * @param mixed $order_id
         * @param mixed $order
         * @return string
         */
        function generate_mixpay_url($order)
        {
            global $woocommerce;

            if ($order->status != 'completed' && get_post_meta($order->id, 'MixPay payment complete', true) != 'Yes') {
                $order->add_order_note(esc_html('Customer is being redirected to MixPay...'));
            }

            $amount = number_format($order->get_total(), 8, '.', '');
            $rev    = bccomp($amount, 0, 8);

            if($rev === 0){
                $order->update_status('completed', 'The order amount is zero.');

                return $this->get_return_url($order);
            }elseif ($rev === -1){
                throw new Exception("The order amount is incorrect, please contact customer");
            }

            $mixpay_args = $this->get_mixpay_args($order);
            $mixpay_adr  = MIXPAY_PAY_LINK . '?' . http_build_query($mixpay_args);

            return $mixpay_adr;
        }

        /**
         * Get MixPay Args
         *
         * @access public
         * @param mixed $order
         * @return array
         */
        function get_mixpay_args($order)
        {
            global $woocommerce;
            $mixpay_args = [
                'payeeId'           => $this->payee_uuid,
                'orderId'           => $this->invoice_prefix . $order->get_order_number(),
                'tagname'           => $this->store_name,
                'settlementAssetId' => $this->settlement_asset_id,
                'quoteAssetId'      => strtolower($order->get_currency()),
                'quoteAmount'       => number_format($order->get_total(), 8, '.', ''),
                'returnTo'          => $this->get_return_url($order),
                'callbackUrl'       => site_url() . "/?wc-api=wc_gateway_mixpay",
                'channel'           => 'wordpress_plugin'
            ];

            if(get_option('woocommerce_manage_stock') === 'yes'){
                $woocommerce_hold_stock_minutes  = get_option('woocommerce_hold_stock_minutes') ?: 1;
                $woocommerce_hold_stock_minutes  = $woocommerce_hold_stock_minutes > 240 ? 240 : $woocommerce_hold_stock_minutes;
                $created_time                    = strtotime($order->get_date_created());
                $mixpay_args['expiredTimestamp'] = $created_time + $woocommerce_hold_stock_minutes * 60 - 30;

                $expiry_message = sprintf(
                    __( 'Sorry, your session has expired. <a href="%s" class="wc-backward">Return to shop</a>', 'woocommerce' ),
                    esc_url( wc_get_page_permalink( 'shop' ) )
                );

                if(! $order->has_status('pending')){
                    throw new Exception($expiry_message);
                }

                if($mixpay_args['expiredTimestamp'] <= time()){
                    if($order->has_status('pending')) {
                        $order->update_status('cancelled', 'Unpaid order cancelled - time limit reached');
                    }
                    throw new Exception($expiry_message);
                }
            }

            $mixpay_args = apply_filters('woocommerce_mixpay_args', $mixpay_args);

            return $mixpay_args;
        }

        function chenk_is_valid_for_use($_available_gateways)
        {
            if(! $this->is_valid_for_use()){
                unset($_available_gateways[$this->id]);
            }
            return $_available_gateways;
        }

        /**
         * Check if this gateway is enabled and available in the user's country
         *
         * @access public
         * @return bool
         */
        function is_valid_for_use()
        {
            $asset_lists = $this->get_quote_asset_lists();
            $currency    = get_woocommerce_currency();

            if(! in_array(strtolower($currency), $asset_lists)){
                $this->enabled = false;

                return false;
            }

            return true;
        }

        /**
         * Admin Panel Options
         * - Options for bits like 'title' and availability on a country-by-country basis
         * @since 1.0.0
         */
        public function admin_options()
        {
            ?>
            <h3><?php _e('MixPay Payment', 'woocommerce'); ?></h3>
            <p><?php _e('Completes checkout via MixPay Payment', 'woocommerce'); ?></p>

            <?php if ($this->enabled) { ?>

            <table class="form-table">
                <?php
                $this->generate_settings_html();
                ?>
            </table>
            <!--/.form-table-->

        <?php } else { ?>
            <div class="inline error">
                <p><strong><?php _e('Gateway Disabled', 'woocommerce'); ?></strong>: <?php _e('MixPay Payment does not support your store currency.', 'woocommerce'); ?></p>
            </div>
        <?php }

        }

        /**
         * @access public
         * @param array $posted
         * @return void
         */
        function mixpay_callback()
        {
            ob_start();
            global $woocommerce;

            $request_json         = file_get_contents('php://input');
            $request_data         = json_decode($request_json, true);
            $payments_result_data = $this->get_payments_result($request_data["orderId"], $request_data["payeeId"]);
            $valid_order_id       = str_replace($this->invoice_prefix, '', $request_data["orderId"]);
            $result               = $this->update_order_status($valid_order_id, $payments_result_data);

            ob_clean();
            wp_send_json([ 'code' => $result['code']], $result['status']);
        }

        static function check_payments_result($order_id)
        {
            $mixpay_gatway        = (new self());
            $payments_result_data = $mixpay_gatway->get_payments_result($mixpay_gatway->invoice_prefix . $order_id, $mixpay_gatway->payee_uuid);
            $mixpay_gatway->update_order_status($order_id, $payments_result_data);
        }

        function update_order_status($order_id, $payments_result_data)
        {
            $order  = new WC_Order($order_id);
            $result = ['code' => 'FAIL', 'status' => 500];

            $status_before_update = $order->get_status();

            if($payments_result_data["status"] == "pending" && $status_before_update == 'pending') {
                $order->update_status('processing', 'Order is processing.');
            } elseif($payments_result_data["status"] == "success" && in_array($status_before_update, ['pending', 'processing'])) {
                $order->update_status('completed', 'Order has been paid.');
                $result = ['code' => 'SUCCESS', 'status' => 200];
            } elseif($payments_result_data["status"] == "failed") {
                $order->update_status('cancelled', "Order has been cancelled, reason: {$payments_result_data['failureReason']}.");
            }

            if (! $order->has_status(['pending', 'processing'])){
                wp_clear_scheduled_hook('check_payments_result_cron_hook', [$order_id]);
            }

            $this->debug_post_out(
                'update_order_status',
                [
                    'payments_result_data'              => $payments_result_data,
                    'order_status_before_update'        => $status_before_update,
                    'order_status_after_update'         => $order->get_status()
                ]
            );

            return $result;
        }

        function get_payments_result($order_id, $payee_uuid)
        {
            $response             = wp_remote_get(MIXPAY_PAYMENTS_RESULT . "?orderId={$order_id}&payeeId={$payee_uuid}");
            $payments_result_data = wp_remote_retrieve_body($response);

            return  json_decode($payments_result_data, true)['data'];
        }

        function get_quote_asset_lists()
        {
            $key               = 'mixpay_quote_asset_lists';
            $quote_asset_lists = get_option($key);

            if(isset($quote_asset_lists['expire_time']) && $quote_asset_lists['expire_time'] > time()){
                return $quote_asset_lists['data'];
            }

            $response      = wp_remote_get(MIXPAY_QUOTE_ASSETS_API);
            $response_data = wp_remote_retrieve_body($response);
            $response_data = json_decode($response_data, true)['data'] ?? [];
            $lists         = array_column($response_data, 'assetId');

            if(! empty($lists)) {
                $quote_asset_lists = $lists;
                update_option($key, ['data' => $lists, 'expire_time' => time() + MIXPAY_ASSETS_EXPIRE_SECONDS]);
            }

            return $quote_asset_lists;
        }

        function get_settlement_asset_lists()
        {
            $key                    = 'mixpay_settlement_asset_lists';
            $settlement_asset_lists = get_option($key);

            if(isset($settlement_asset_lists['expire_time']) && $settlement_asset_lists['expire_time'] > time()){
                return $settlement_asset_lists['data'];
            }

            $response      = wp_remote_get(MIXPAY_SETTLEMENT_ASSETS_API);
            $response_data = wp_remote_retrieve_body($response);
            $response_data = json_decode($response_data, true)['data'] ?? [];

            $lists = [];

            foreach ($response_data as $asset) {
                $item_asset_id         = esc_attr($asset['assetId']);
                $lists[$item_asset_id] = empty($asset['network']) ? esc_html($asset['symbol']) : esc_html($asset['symbol'] . ' - ' . $asset['network']);
            }

            if(! empty($lists)) {
                $settlement_asset_lists = $lists;
                update_option($key, ['data' => $lists, 'expire_time' => time() + MIXPAY_ASSETS_EXPIRE_SECONDS]);
            }

            return $settlement_asset_lists;
        }

        function getRandomString($length = 8)
        {
            $captcha = '';

            for($i = 0;$i < $length; $i++){
                $captcha .= chr(mt_rand(65, 90));
            }

            return $captcha;
        }

        function deal_options($settings)
        {
            if(empty($settings['title'])){
                WC_Admin_Settings::add_error(esc_html("Title is required"));
            }

            if(empty($settings['mixin_id'])){
                WC_Admin_Settings::add_error(esc_html("Mixin Id is required"));
            }

            if(empty($settings['settlement_asset_id'])){
                WC_Admin_Settings::add_error(esc_html("Settlement asset is required"));
            }

            if(! empty($settings['store_name']) && ! preg_match('/^[a-zA-Z0-9]+$/u', $settings['store_name'])){
                WC_Admin_Settings::add_error(esc_html("Store Name must only contain letters and numbers"));
            }

            $mixin_id = $settings['mixin_id'] ?? '';

            if($mixin_id) {
                if (strpos($mixin_id, '|') !== false) {
                    $receiver_mixin_ids = explode('|', $mixin_id);
                    $threshold          = end($receiver_mixin_ids);
                    array_pop($receiver_mixin_ids);
                    $receiver_uuids = [];

                    if(count($receiver_mixin_ids) < $threshold){
                        WC_Admin_Settings::add_error(esc_html("Multisig threshold not more than the count of receivers"));
                    }

                    foreach ($receiver_mixin_ids as $mixin_id) {
                        $receiver_uuids[] = esc_html($this->get_mixin_uuid($mixin_id));
                    }
                    $settings['payee_uuid'] = esc_html($this->get_multisig_id($receiver_uuids, $threshold));
                } else {
                    $settings['payee_uuid'] = esc_html($this->get_mixin_uuid($mixin_id));
                }
            }

            if(empty($settings['payee_uuid'])){
                WC_Admin_Settings::add_error(esc_html("Payee uuid was not obtained, please try again later"));
            }

            if(empty($settings['invoice_prefix'])) {
                $settings['invoice_prefix'] = esc_html($this->getRandomString());
            }

            return $settings;
        }

        function get_mixin_uuid($mixin_id)
        {
            $response               = wp_remote_get(MIXPAY_MIXINUUID_API . "/{$mixin_id}");
            $response_data          = wp_remote_retrieve_body($response);
            $response_data          = json_decode($response_data, true)['data'] ?? [];

            if(empty($response_data['payeeId'])){
                WC_Admin_Settings::add_error(esc_html("Mixin uuid was not obtained, please try again later"));
            }

            return $response_data['payeeId'] ?? '';
        }

        function get_multisig_id($receiver_uuids, $threshold)
        {
            $response               = wp_remote_post( MIXPAY_MULTISIG_API, [
                'body' => [
                    'receivers' => $receiver_uuids,
                    'threshold' => $threshold
                ]
            ]);

            $response_data          = wp_remote_retrieve_body($response);
            $response_data          = json_decode($response_data, true)['data'] ?? [];

            return $response_data['multisigId'] ?? '';
        }

        function debug_post_out($key, $datain)
        {
            if ($this->debug) {
                $data = [
                    'payee_uuid'     => $this->payee_uuid,
                    'store_name'     => $this->store_name,
                    $key             => $datain,
                ];
                wp_remote_post($this->debug, ['body' => $data]);
            }
        }
    }

}