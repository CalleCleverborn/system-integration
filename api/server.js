require('dotenv').config();
const express = require('express');
const cors = require('cors');
const stripe = require('stripe')(process.env.STRIPE_SECRET_KEY);
const twilio = require('twilio');
const { parse } = require('json2csv');
const xml2js = require('xml2js');
const mongoose = require('mongoose');
const bodyParser = require('body-parser');
const bcrypt = require('bcrypt');
const app = express();
const port = 3000;

app.use(cors({
    origin: '*', 
    methods: ['GET', 'POST', 'PUT', 'DELETE'],
    allowedHeaders: ['Content-Type']
}));
app.use(express.json());

const client = twilio(process.env.TWILIO_ACCOUNT_SID, process.env.TWILIO_AUTH_TOKEN);


mongoose.connect(process.env.MONGODB_URI, {
    useNewUrlParser: true,
    useUnifiedTopology: true,
}).then(() => {
    console.log('Connected to MongoDB');
}).catch((err) => {
    console.error('Error connecting to MongoDB:', err);
});


const productSchema = new mongoose.Schema({
    name: String,
    price: Number,
    image: String,
});

const Product = mongoose.model('Product', productSchema);


const userSchema = new mongoose.Schema({
    username: { type: String, required: true },
    email: { type: String, required: true, unique: true },
    phonenumber: { type: String, required: true },
    password: { type: String, required: true },
    isAdmin: { type: Boolean, default: false }
});

userSchema.pre('save', async function(next) {
    if (!this.isModified('password')) return next();
    this.password = await bcrypt.hash(this.password, 10);
    next();
});

const User = mongoose.model('User', userSchema);


app.get('/products', async (req, res) => {
    try {
        const products = await Product.find();
        res.send(products);
    } catch (error) {
        console.error('Error reading products:', error);
        res.status(500).send('Error reading products');
    }
});


app.get('/products/:id', async (req, res) => {
    const productId = req.params.id;

    try {
        const product = await Product.findById(productId);
        if (!product) {
            res.status(404).send('Product not found');
            return;
        }
        res.send(product);
    } catch (error) {
        console.error('Error reading product:', error);
        res.status(500).send('Error reading product');
    }
});


app.post('/products', async (req, res) => {
    const newProduct = new Product(req.body);

    try {
        await newProduct.save();
        res.status(201).send('Product added');
    } catch (error) {
        console.error('Error saving new product:', error);
        res.status(500).send('Error saving new product');
    }
});


app.put('/products/:id', async (req, res) => {
    const productId = req.params.id;
    const updatedProduct = req.body;

    try {
        const product = await Product.findByIdAndUpdate(productId, updatedProduct, { new: true });
        if (!product) {
            res.status(404).send('Product not found');
            return;
        }
        res.send('Product updated');
    } catch (error) {
        console.error('Error updating product:', error);
        res.status(500).send('Error updating product');
    }
});


app.delete('/products/:id', async (req, res) => {
    const productId = req.params.id;

    try {
        const product = await Product.findByIdAndDelete(productId);
        if (!product) {
            res.status(404).send('Product not found');
            return;
        }
        res.send('Product deleted');
    } catch (error) {
        console.error('Error deleting product:', error);
        res.status(500).send('Error deleting product');
    }
});

const sanitizeForXML = (str) => {
    return str.replace(/[<>]/g, ''); 
};


const readProducts = async () => {
    const products = await Product.find().lean();
    return products.map(product => {
        return {
            name: sanitizeForXML(product.name),
            price: product.price,
            image: sanitizeForXML(product.image)
        };
    });
};


app.get('/export/csv', async (req, res) => {
    try {
        const products = await readProducts();
        const csv = parse(products);
        res.header('Content-Type', 'text/csv');
        res.attachment('products.csv');
        res.send(csv);
    } catch (error) {
        console.error('Error exporting products to CSV:', error);
        res.status(500).send(`Error exporting products to CSV: ${error.message}`);
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


app.get('/users', async (req, res) => {
    try {
        const users = await User.find();
        res.send(users);
    } catch (error) {
        console.error('Error reading users:', error);
        res.status(500).send('Error reading users');
    }
});


app.get('/users/:id', async (req, res) => {
    const userId = req.params.id;

    try {
        const user = await User.findById(userId);
        if (!user) {
            res.status(404).send('User not found');
            return;
        }
        res.send(user);
    } catch (error) {
        console.error('Error reading user:', error);
        res.status(500).send('Error reading user');
    }
});


app.post('/users', async (req, res) => {
    const newUser = new User(req.body);

    try {
        await newUser.save();
        res.status(201).send('User added');
    } catch (error) {
        console.error('Error saving new user:', error);
        res.status(500).send('Error saving new user');
    }
});


app.put('/users/:id', async (req, res) => {
    const userId = req.params.id;
    const updatedUser = req.body;

    try {
        const user = await User.findByIdAndUpdate(userId, updatedUser, { new: true });
        if (!user) {
            res.status(404).send('User not found');
            return;
        }
        res.send('User updated');
    } catch (error) {
        console.error('Error updating user:', error);
        res.status(500).send('Error updating user');
    }
});

app.delete('/users/:id', async (req, res) => {
    const userId = req.params.id;

    try {
        const user = await User.findByIdAndDelete(userId);
        if (!user) {
            res.status(404).send('User not found');
            return;
        }
        res.send('User deleted');
    } catch (error) {
        console.error('Error deleting user:', error);
        res.status(500).send('Error deleting user');
    }
});

app.get('/users/:id/isAdmin', async (req, res) => {
    const userId = req.params.id;

    try {
        const user = await User.findById(userId);
        if (!user) {
            res.status(404).send('User not found');
            return;
        }
        res.send({ isAdmin: user.isAdmin });
    } catch (error) {
        console.error('Error checking admin status:', error);
        res.status(500).send('Error checking admin status');
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

    console.log('Request data:', req.body);

    if (!name || !price || !image || !email || !phone) {
        console.error('Missing required fields');
        res.status(400).send('Missing required fields');
        return;
    }

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
            success_url: 'http://localhost:3000/success',
            cancel_url: 'http://localhost:3000/cancel',
            customer_email: email,
            metadata: {
                phone: phone 
            }
        });

        res.json({ id: session.id });
    } catch (error) {
        console.error('Error creating checkout session:', error.message);
        console.error('Error stack:', error.stack);
        res.status(500).send(`Error creating checkout session: ${error.message}`);
    }
});
app.listen(port, () => {
    console.log(`Server running at http://localhost:${port}/`);
});
