mod restaurants;

fn main() {
    println!("Welcome to Ramen Simulator!");
    let rest = restaurants::Restaurant {
        name: "ichiraku".to_owned(),
        cash: 0.,
    };
    println!("{:?}", rest);
}
