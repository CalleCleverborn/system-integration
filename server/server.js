require('dotenv').config();
const express = require('express');
const cors = require('cors');
const fs = require('fs');
const stripe = require('stripe')(process.env.STRIPE_SECRET_KEY);
const twilio = require('twilio');
const { parse } = require('json2csv');
const xml2js = require('xml2js');
const bodyParser = require('body-parser');
const app = express();
const port = 3000;

app.use(cors());
app.use(express.json());

// Twilio client setup
const client = twilio(process.env.TWILIO_ACCOUNT_SID, process.env.TWILIO_AUTH_TOKEN);

// Helper functions to read and write products
function readProducts() {
    return new Promise((resolve, reject) => {
        fs.readFile('products.json', 'utf8', (err, data) => {
            if (err) {
                reject(err);
            } else {
                try {
                    const products = data ? JSON.parse(data) : [];
                    resolve(products);
                } catch (jsonError) {
                    reject(jsonError);
                }
            }
        });
    });
}

function writeProducts(products) {
    return new Promise((resolve, reject) => {
        fs.writeFile('products.json', JSON.stringify(products, null, 2), (err) => {
            if (err) {
                reject(err);
            } else {
                resolve();
            }
        });
    });
}

// API endpoints
app.get('/products', async (req, res) => {
    try {
        const products = await readProducts();
        res.send(products);
    } catch (error) {
        console.error('Error reading products:', error);
        res.status(500).send('Error reading products');
    }
});

app.get('/products/:id', async (req, res) => {
    const productId = parseInt(req.params.id, 10);

    try {
        const products = await readProducts();
        const product = products.find(p => p.id === productId);
        if (!product) {
            res.status(404).send('Product not found');
            return;
        }
        res.send(product);
    } catch (error) {
        console.error('Error reading products:', error);
        res.status(500).send('Error reading products');
    }
});

app.post('/products', async (req, res) => {
    const newProduct = req.body;

    try {
        const products = await readProducts();
        const newId = products.length ? Math.max(...products.map(p => p.id)) + 1 : 1;
        newProduct.id = newId;
        products.push(newProduct);
        await writeProducts(products);
        console.log('Product added:', newProduct);
        console.log('All products:', products);
        res.status(201).send('Product added');
    } catch (error) {
        console.error('Error saving new product:', error);
        res.status(500).send('Error saving new product');
    }
});

app.put('/products/:id', async (req, res) => {
    const productId = parseInt(req.params.id);
    const updatedProduct = req.body;

    try {
        const products = await readProducts();
        const productIndex = products.findIndex(p => p.id === productId);
        if (productIndex === -1) {
            res.status(404).send('Product not found');
            return;
        }

        products[productIndex] = { ...products[productIndex], ...updatedProduct };
        await writeProducts(products);
        res.send('Product updated');
    } catch (error) {
        console.error('Error updating product:', error);
        res.status(500).send('Error updating product');
    }
});

app.delete('/products/:id', async (req, res) => {
    const productId = parseInt(req.params.id);

    try {
        const products = await readProducts();
        const updatedProducts = products.filter(p => p.id !== productId);
        if (products.length === updatedProducts.length) {
            res.status(404).send('Product not found');
            return;
        }

        await writeProducts(updatedProducts);
        res.send('Product deleted');
    } catch (error) {
        console.error('Error deleting product:', error);
        res.status(500).send('Error deleting product');
    }
});

app.get('/export/csv', async (req, res) => {
    try {
        const products = await readProducts();
        const csv = parse(products);
        res.header('Content-Type', 'text/csv');
        res.attachment('products.csv');
        res.send(csv);
    } catch (error) {
        console.error('Error exporting products to CSV:', error);
        res.status(500).send('Error exporting products to CSV');
    }
});

app.get('/export/xml', async (req, res) => {
    try {
        const products = await readProducts();
        const builder = new xml2js.Builder();
        const xml = builder.buildObject({ products });
        res.header('Content-Type', 'application/xml');
        res.attachment('products.xml');
        res.send(xml);
    } catch (error) {
        console.error('Error exporting products to XML:', error);
        res.status(500).send('Error exporting products to XML');
    }
});

app.post('/create-payment-intent', async (req, res) => {
    const { amount, currency } = req.body;

    try {
        const paymentIntent = await stripe.paymentIntents.create({
            amount,
            currency,
        });
        res.send({
            clientSecret: paymentIntent.client_secret,
        });
    } catch (error) {
        console.error('Error creating payment intent:', error);
        res.status(500).send('Error creating payment intent');
    }
});

app.post('/create-checkout-session', async (req, res) => {
    const { name, price, image, email, phone } = req.body;

    const lineItems = [{
        price_data: {
            currency: 'usd',
            product_data: {
                name: name,
                images: [image],
            },
            unit_amount: parseInt(price) * 100,
        },
        quantity: 1,
    }];

    try {
        const session = await stripe.checkout.sessions.create({
            payment_method_types: ['card'],
            line_items: lineItems,
            mode: 'payment',
            customer_email: email,
            success_url: 'http://localhost:8000/success.php',
            cancel_url: 'http://localhost:8000/cancel.php',
        });

        res.json({ id: session.id });
    } catch (error) {
        console.error('Error creating checkout session:', error);
        res.status(500).send('Error creating checkout session');
    }
});

// Stripe webhook endpoint to listen for checkout session completion
app.post('/webhook', bodyParser.raw({ type: 'application/json' }), (request, response) => {
    const sig = request.headers['stripe-signature'];

    let event;
    try {
        event = stripe.webhooks.constructEvent(request.body, sig, process.env.STRIPE_WEBHOOK_SECRET);
    } catch (err) {
        console.log(`⚠️  Webhook signature verification failed.`, err.message);
        return response.sendStatus(400);
    }

    // Handle the checkout.session.completed event
    if (event.type === 'checkout.session.completed') {
        const session = event.data.object;

        // Log the event for debugging
        console.log('Checkout session completed:', session);

        // Retrieve the session and use the customer details to send SMS
        stripe.checkout.sessions.retrieve(session.id, { expand: ['line_items', 'customer'] })
            .then((retrievedSession) => {
                console.log('Retrieved session:', retrievedSession);

                const phoneNumber = session.customer_details.phone;
                if (phoneNumber) {
                    client.messages
                        .create({
                            body: 'Thank you for your purchase!',
                            from: process.env.TWILIO_PHONE_NUMBER,
                            to: phoneNumber,
                        })
                        .then(message => console.log(`SMS sent with SID: ${message.sid}`))
                        .catch(error => console.error('Error sending SMS:', error));
                } else {
                    console.log('No phone number provided.');
                }
            })
            .catch(error => console.error('Error retrieving session:', error));
    }

    // Return a response to acknowledge receipt of the event
    response.json({ received: true });
});

app.listen(port, () => {
    console.log(`Server running at http://localhost:${port}/`);
});
